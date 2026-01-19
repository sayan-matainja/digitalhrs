<?php

namespace App\Repositories;

use App\Enum\LeaveStatusEnum;
use App\Helpers\AppHelper;
use App\Models\TimeLeave;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeLeaveRepository
{
    public function getAllEmployeeTimeLeaveRequest($filterParameters, $select = ['*'], $with = [])
    {

        $leaveDetailList = TimeLeave::with($with)
            ->select($select)
            ->when(isset($filterParameters['requested_by']), function ($query) use ($filterParameters) {
                $query->where('requested_by', $filterParameters['requested_by']);
            })
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->where('department_id', $filterParameters['department_id']);
            })

            ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
                $query->where('status', $filterParameters['status']);
            });
        if (isset($filterParameters['start_date'])) {
            $leaveDetailList
                ->whereBetween('issue_date', [$filterParameters['start_date'], $filterParameters['end_date']]);
        } else {
            $leaveDetailList
                ->when(isset($filterParameters['month']) || isset($filterParameters['year']), function ($query) use ($filterParameters) {
                    if(isset($filterParameters['month'])){
                        $query->whereMonth('issue_date', '=', $filterParameters['month']);
                    }
                    if(isset($filterParameters['year'])){
                        $query->whereYear('issue_date', '=', $filterParameters['year']);
                    }

                });
        }
        return $leaveDetailList
            ->orderBy('updated_at', 'DESC')
            ->paginate( getRecordPerPage());
    }

    public function getAllTimeLeaveRequestDetailOfEmployee($filterParameters)
    {
        $leaveDetailList = TimeLeave::select(
                'time_leaves.id',
                DB::raw("DATE_FORMAT(CONCAT(time_leaves.issue_date, ' ', time_leaves.start_time), '%Y-%m-%d %H:%i:%s') AS leave_from"),
                DB::raw("DATE_FORMAT(CONCAT(time_leaves.issue_date, ' ', time_leaves.end_time), '%Y-%m-%d %H:%i:%s') AS leave_to"),
                'time_leaves.created_at as leave_requested_date',
                'time_leaves.status',
                'time_leaves.reasons as leave_reason',
                'time_leaves.admin_remark',
                'time_leaves.requested_by',
                'time_leaves.request_updated_by',
            )
            ->addSelect(DB::raw("0 as leave_type_id"))
            ->addSelect(DB::raw("1 as no_of_days"))
            ->addSelect(DB::raw("1 as early_exit"))
            ->addSelect(DB::raw("'Time Leave' as leave_type_name"))

            ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
                $query->where('status', $filterParameters['status']);
            })
            ->where('requested_by', $filterParameters['user_id'])
            ->whereBetween('issue_date', [$filterParameters['start_date'], $filterParameters['end_date']]);

        return $leaveDetailList->orderBy('id', 'DESC')
            ->get();
    }

    public function findEmployeeLeaveRequestByEmployeeId($leaveRequestId, $select = ['*'])
    {
        return TimeLeave::select($select)
            ->where('id', $leaveRequestId)
            ->first();
    }

    public function findLeaveRequestReasonByEmployeeId($leaveRequestId)
    {
        $time =  TimeLeave::select('time_leaves.reasons', 'time_leaves.admin_remark','users.name')
            ->leftJoin('users','time_leaves.referred_by','=','users.id')
            ->where('time_leaves.id',$leaveRequestId)
            ->first();

        return $time;
    }

    public function getEmployeeLatestTimeLeave($date)
    {
        return TimeLeave::query()
            ->whereDate('issue_date', $date)
            ->whereIn('status', [LeaveStatusEnum::pending->value, LeaveStatusEnum::approved->value])
            ->where('requested_by', getAuthUserCode())
            ->first();
    }

    public function getEmployeeApprovedTimeLeave($date, $userId='')
    {
        $timeLeave =  TimeLeave::query()
            ->whereDate('issue_date', $date)
            ->where('status', LeaveStatusEnum::approved->value);

        if(!empty($userId)){
            $timeLeave = $timeLeave->where('requested_by', $userId);
        }else{
            $timeLeave = $timeLeave->where('requested_by', getAuthUserCode());
        }

        return $timeLeave->first();
    }

    public function getTimeLeaveWithLeaveTakenbyEmployee($filterParameters)
    {

        $query = TimeLeave::query()
            ->addSelect(DB::raw("0 as leave_type_id"))
            ->addSelect(DB::raw("'Time Leave' as leave_type_name"))
            ->addSelect(DB::raw("'time-leave' as leave_type_slug"))
            ->addSelect(DB::raw("0 as total_leave_allocated"))
            ->selectRaw('COUNT(time_leaves.id) as leave_taken')

            ->where("time_leaves.requested_by", getAuthUserCode())
            ->where("time_leaves.status", 'approved');

        if (isset($filterParameters['start_date'])) {
            $query->whereBetween('time_leaves.issue_date', [$filterParameters['start_date'], $filterParameters['end_date']]);
        } else {
            $query->whereYear('time_leaves.issue_date', $filterParameters['year']);
        }

        return $query->first();
    }

    public function store($validatedData)
    {
        return TimeLeave::create($validatedData)->fresh();
    }

    public function update($timeLeaveDetail, $validatedData)
    {
        return $timeLeaveDetail->update($validatedData);
    }


    public function getLeaveCountDetailOfEmployeeOfTwoMonth()
    {
        $date = AppHelper::getStartEndDateForLeaveCalendar();

        return TimeLeave::select('issue_date',DB::raw('count(id) as leave_count'))
            ->whereHas('leaveRequestedBy', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->where('status', LeaveStatusEnum::approved->value)
            ->where(function ($query) use ($date) {
                $query->whereBetween('issue_date', [$date['start_date'], $date['end_date']])
                    ->orWhereBetween('issue_date', [$date['start_date'], $date['end_date']]);
            })
            ->groupBy('issue_date')
            ->orderBy('issue_date')
            ->get();
    }

    public function getAllEmployeeTimeLeaveDetailBySpecificDay($filterParameter)
    {


        $date = AppHelper::getStartEndDateForLeaveCalendar();
        return  TimeLeave::select(
            'users.id as user_id',
            'users.name as name',
            'users.avatar as avatar',
            'departments.dept_name as department',
            'posts.post_name as post',
            'time_leaves.issue_date',
            'time_leaves.start_time as leave_from',
            'time_leaves.end_time as leave_to',
            'time_leaves.status as leave_status'
        )
            ->Join('users', function ($join) {
                $join->on('time_leaves.requested_by', '=', 'users.id')
                    ->whereNUll('users.deleted_at');
            })
            ->join('departments', 'departments.id', '=', 'users.department_id')
            ->join('posts', 'posts.id', '=', 'users.post_id')
            ->where(function ($query) use ($filterParameter) {
                $query->whereDate('time_leaves.issue_date', '=', $filterParameter['leave_date']);
            })
            ->where(function ($query) use ($date) {
                $query->whereBetween('time_leaves.issue_date', [$date['start_date'], $date['end_date']]);
            })

            ->where('time_leaves.status', 'approved')
            ->orderBy('time_leaves.issue_date')
            ->get();

    }

    public function getAllEmployeeTimeLeaveDetail($filterParameter)
    {


        $date = AppHelper::getStartEndDateForLeaveCalendar();
        return  TimeLeave::select(
            'users.id as user_id',
            'users.name as name',
            'users.avatar as avatar',
            'departments.dept_name as department',
            'posts.post_name as post',
            'time_leaves.issue_date',
            'time_leaves.start_time as leave_from',
            'time_leaves.end_time as leave_to',
            'time_leaves.status as leave_status'
        )
            ->Join('users', function ($join) {
                $join->on('time_leaves.requested_by', '=', 'users.id')
                    ->whereNUll('users.deleted_at');
            })
            ->join('departments', 'departments.id', '=', 'users.department_id')
            ->join('posts', 'posts.id', '=', 'users.post_id')
            ->where(function ($query) use ($filterParameter) {
                $query->whereBetween('time_leaves.issue_date', [$filterParameter['start_date'], $filterParameter['end_date']]);
            })
            ->where(function ($query) use ($date) {
                $query->whereBetween('time_leaves.issue_date', [$date['start_date'], $date['end_date']]);
            })

            ->where('time_leaves.status', 'approved')
            ->orderBy('time_leaves.issue_date')
            ->get();

    }

    public function checkEmployeeTimeLeave($employeeId, $dates)
    {
        return TimeLeave::query()
            ->whereBetween('issue_date', [$dates['leave_from'],$dates['leave_to']])
            ->whereIn('status', [LeaveStatusEnum::pending->value, LeaveStatusEnum::approved->value])
            ->where('requested_by', $employeeId)
            ->exists();
    }
}

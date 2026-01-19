<?php

namespace App\Repositories;

use App\Enum\LeaveGenderEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Models\EmployeeLeaveType;
use App\Models\LeaveRequestMaster;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeaveTypeRepository
{
    public function getAllLeaveTypesWithLeaveTakenbyEmployee($filterParameters)
    {

        $authUserGender = auth()->user()->gender;


        return LeaveType::query()
            ->select(
                'leave_types.id as leave_type_id',
                'leave_types.name as leave_type_name',
                'leave_types.slug as leave_type_slug',
                'leave_types.is_active as leave_type_status',
                'leave_types.early_exit as early_exit',
                'leave_types.company_id as company_id',
                'leave_requests_master.status',
                'leave_requests_master.requested_by',
                DB::raw('COALESCE(employee_leave_types.days, leave_types.leave_allocated, 0) as total_leave_allocated'),
                DB::raw('IFNULL(sum(leave_requests_master.no_of_days),0) as leave_taken')
            )
            ->leftJoin('employee_leave_types', function ($join) {
                $join->on('leave_types.id', '=', 'employee_leave_types.leave_type_id')
                    ->where('employee_leave_types.employee_id', '=', getAuthUserCode())
                    ->where('employee_leave_types.is_active', '=', 1);
            })
            ->leftJoin('leave_requests_master', function ($join) use ($filterParameters) {
                $join->on('leave_types.id', '=', 'leave_requests_master.leave_type_id')
                    ->where('leave_requests_master.requested_by', getAuthUserCode())
                    ->where('leave_requests_master.status', 'approved');

                $join->where(function ($query) use ($filterParameters) {
                    if (!empty($filterParameters['start_date']) && !empty($filterParameters['end_date'])) {
                        $startDate = $filterParameters['start_date'];
                        $endDate   = $filterParameters['end_date'];

                        $query->where('leave_requests_master.leave_from', '<=', $endDate)
                            ->where(function ($q) use ($startDate) {
                                $q->whereNull('leave_requests_master.leave_to')
                                ->orWhere('leave_requests_master.leave_to', '>=', $startDate);
                            });
                    } else {

                        $year = $filterParameters['year'] ?? now()->year;

                        $query->whereYear('leave_requests_master.leave_from', $year)
                            ->orWhere(function ($q) use ($year) {
                                $q->whereNotNull('leave_requests_master.leave_to')
                                    ->whereYear('leave_requests_master.leave_to', $year);
                            });
                    }
                });
            })

            ->when(isset($authUserGender), function ($query) use ($authUserGender) {
                $query->where('leave_types.gender', $authUserGender)
                    ->orWhere('leave_types.gender', '=', LeaveGenderEnum::all->value);
            })
            ->groupBy(
                'leave_types.id',
                'leave_types.name',
                'leave_types.leave_allocated',
                'leave_types.slug',
                'leave_types.company_id',
                'leave_requests_master.status',
                'leave_requests_master.requested_by',
                'leave_types.is_active',
                'leave_types.early_exit',
            )
            ->orderBy('leave_types.id', 'ASC')
            ->get();
    }

//    public function getAllLeaveTypesWithLeaveTaken($filterParameters)
//    {
//        $year = $filterParameters['year'];
//        $dates = AppHelper::getYearDates($year);
//
//        $query = User::query()
//            ->where('is_active', 1)
//            ->when($filterParameters['branch_id'] ?? null, fn($q) => $q->where('branch_id', $filterParameters['branch_id']))
//            ->when($filterParameters['department_id'] ?? null, fn($q) => $q->where('department_id', $filterParameters['department_id']))
//            ->when($filterParameters['requested_by'] ?? null, fn($q) => $q->where('id', $filterParameters['requested_by']))
//            ->orderBy('name');
//
//        $leaveTypes = LeaveType::where('is_active', 1)->get(['id', 'name', 'slug', 'leave_allocated', 'gender']);
//
//        $results = [];
//
//        $query->chunkById(100, function ($users) use ($leaveTypes, $dates, $filterParameters, $year, &$results) {
//            foreach ($users as $user) {
//                if (
//                    $filterParameters['leave_type'] ?? null &&
//                !in_array($filterParameters['leave_type'], $leaveTypes->pluck('id')->toArray())
//                ) continue;
//
//                foreach ($leaveTypes as $leaveType) {
//                    // Skip gender-specific leave if not applicable
//                    if ($leaveType->gender !== 'all' && $leaveType->gender !== $user->gender) {
//                        continue;
//                    }
//
//                    if ($filterParameters['leave_type'] ?? null && $leaveType->id != $filterParameters['leave_type']) {
//                        continue;
//                    }
//
//                    $allocated = $user->employeeLeaveTypes()
//                        ->where('leave_type_id', $leaveType->id)
//                        ->where('is_active', 1)
//                        ->value('days') ?? $leaveType->leave_allocated ?? 0;
//
//                    $used = LeaveRequestMaster::where('requested_by', $user->id)
//                        ->where('leave_type_id', $leaveType->id)
//                        ->where('status', 'approved')
//                        ->where(function ($q) use ($dates) {
//                            $q->whereBetween('leave_from', [$dates['start_date'], $dates['end_date']])
//                                ->orWhereBetween('leave_to', [$dates['start_date'], $dates['end_date']])
//                                ->orWhereNull('leave_to');
//                        })->sum('no_of_days');
//
//                    $remaining = max($allocated - $used, 0);
//
//                    $results[] = (object) [
//                        'employee_id' => $user->id,
//                        'employee_name' => $user->name,
//                        'year' => $year,
//                        'leave_type_id' => $leaveType->id,
//                        'leave_type_name' => $leaveType->name,
//                        'leave_type_slug' => $leaveType->slug,
//                        'allocated' => $allocated,
//                        'used' => $used,
//                        'remaining' => $remaining,
//                    ];
//                }
//            }
//        });
//
//        // Sort final result
//        $collection = collect($results)->sortBy([
//            ['employee_name', 'asc'],
//            ['leave_type_name', 'asc']
//        ]);
//
//        return $collection;
//    }
    public function getAllLeaveTypesWithLeaveTaken($filterParameters)
    {
        $year = $filterParameters['year'];
        $dates = AppHelper::getYearDates($year);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];

        $users = User::where('is_active', 1)
            ->when($filterParameters['branch_id'] ?? null, fn($q) => $q->where('branch_id', $filterParameters['branch_id']))
            ->when($filterParameters['department_id'] ?? null, fn($q) => $q->where('department_id', $filterParameters['department_id']))
            ->when($filterParameters['requested_by'] ?? null, fn($q) => $q->where('id', $filterParameters['requested_by']))
            ->select('id', 'name', 'gender', 'branch_id')
            ->get();

        if ($users->isEmpty()) {
            return collect();
        }

        $userIds = $users->pluck('id');

        $leaveTypes = LeaveType::where('is_active', 1)
            ->when(!empty($filterParameters['leave_type']), fn($q) => $q->where('id', $filterParameters['leave_type']))
            ->get(['id', 'name', 'slug', 'leave_allocated', 'gender', 'branch_id'])
            ->groupBy(fn($lt) => $lt->branch_id ?? 'global');

        if ($leaveTypes->isEmpty()) {
            return collect();
        }

        $allLeaveTypeIds = $leaveTypes->flatten()->pluck('id')->unique();


        $employeeLeaveTypes = EmployeeLeaveType::where('is_active', 1)
            ->whereIn('employee_id', $userIds)
            ->get()
            ->keyBy(fn($e) => $e->employee_id . '-' . $e->leave_type_id);

        $usedLeaves = LeaveRequestMaster::where('status', 'approved')
            ->whereIn('requested_by', $userIds)
            ->whereIn('leave_type_id', $allLeaveTypeIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('leave_from', [$startDate, $endDate])
                    ->orWhereBetween('leave_to', [$startDate, $endDate])
                    ->orWhereNull('leave_to');
            })
            ->selectRaw('requested_by, leave_type_id, SUM(no_of_days) as used')
            ->groupBy('requested_by', 'leave_type_id')
            ->get()
            ->keyBy(fn($u) => $u->requested_by . '-' . $u->leave_type_id);


        $results = collect();

        foreach ($users as $user) {

            $branchKey = $user->branch_id ?? 'global';

            // Leave types only for this branch + global
            $branchLeaves = $leaveTypes->get($branchKey, collect());
            $globalLeaves = $leaveTypes->get('global', collect());

            $applicableLeaveTypes = $branchLeaves
                ->merge($globalLeaves)
                ->unique('id');

            foreach ($applicableLeaveTypes as $lt) {

                // Gender restriction
                if ($lt->gender !== 'all' && $lt->gender !== $user->gender) {
                    continue;
                }

                $key = $user->id . '-' . $lt->id;

                // Custom allocated override
                $allocated = $employeeLeaveTypes->has($key)
                    ? $employeeLeaveTypes->get($key)->days
                    : ($lt->leave_allocated ?? 0);

                $used = $usedLeaves->get($key)->used ?? 0;
                $remaining = max($allocated - $used, 0);

                $results->push((object)[
                    'employee_id'       => $user->id,
                    'employee_name'     => $user->name,
                    'year'              => $year,
                    'leave_type_id'     => $lt->id,
                    'leave_type_name'   => $lt->name,
                    'leave_type_slug'   => $lt->slug,
                    'allocated'         => $allocated,
                    'used'              => $used,
                    'remaining'         => $remaining,
                ]);
            }
        }

        return $results->sortBy(['employee_name', 'leave_type_name'])->values();
    }


    public function getAllLeaveTypes($filterParameters, $select = ['*'], $with = [])
    {
        return LeaveType::with($with)
            ->select($select)
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['type']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%' . $filterParameters['type'] . '%');
            })
            ->get();
    }

    public function exportLeaveTypes($select = ['*'], $with = [])
    {
        return LeaveType::with($with)
            ->select($select)
            ->get();
    }

    public function getAllActiveLeaveTypes($select = ['*'])
    {
        return LeaveType::select($select)
            ->where('is_active', 1)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getAllActiveLeaveTypeByBranch($branchId, $select = ['*'])
    {
        return LeaveType::select($select)
            ->where('is_active', 1)
            ->where('branch_id', $branchId)
            ->get();
    }

    public function getPaidLeaveTypes()
    {
        return LeaveType::whereNotNUll('leave_allocated')
            ->select('name', 'id')
            ->orderBy('id')
            ->get();
    }


    public function store($validatedData)
    {
        $validatedData['slug'] = Str::slug($validatedData['name']).'-'.$validatedData['branch_id'];
        return LeaveType::create($validatedData)->fresh();
    }

    public function update($leaveTypeDetail, $validatedData)
    {
        return $leaveTypeDetail->update($validatedData);
    }

    public function delete($leaveTypeDetail)
    {
        return $leaveTypeDetail->delete();
    }

    public function toggleStatus($id)
    {
        $leaveTypeDetail = $this->findLeaveTypeDetailById($id);
        return $leaveTypeDetail->update([
            'is_active' => !$leaveTypeDetail->is_active,
        ]);
    }

    public function findLeaveTypeDetailById($id, $select = ['*'])
    {
        return LeaveType::select($select)->where('id', $id)->firstorFail();
    }

    public function findLeaveTypeDetail($id, $employeeId)
    {
        return LeaveType::select(
            'leave_types.name',
            DB::raw('COALESCE(employee_leave_types.days, leave_types.leave_allocated, 0) as leave_allocated'),
            'leave_types.leave_allocated as is_paid'
        )
            ->leftJoin('employee_leave_types', function ($join) use ($employeeId) {
                $join->on('leave_types.id', '=', 'employee_leave_types.leave_type_id')
                    ->where('employee_leave_types.employee_id', '=', $employeeId)
                    ->where('employee_leave_types.is_active', '=', 1);
            })->where('leave_types.id', $id)->firstorFail();
    }

    public function toggleEarlyExitStatus($id)
    {
        $leaveTypeDetail = $this->findLeaveTypeDetailById($id);
        return $leaveTypeDetail->update([
            'early_exit' => !$leaveTypeDetail->early_exit,
        ]);
    }


    public function getAllLeaveTypesBasedOnEarlyExitStatus($earlyExitStatus)
    {
        return LeaveType::where('is_active', LeaveType::IS_ACTIVE)
            ->when($earlyExitStatus, function ($query) use ($earlyExitStatus) {
                return $query->where('early_exit', $earlyExitStatus);
            })
            ->pluck('name', 'id')
            ->toArray();
    }


    public function getGenderSpecificPaidLeaveTypes($branchId, $gender)
    {
        return LeaveType::whereNotNUll('leave_allocated')
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                    ->orWhere('gender', LeaveGenderEnum::all->value);
            })
            ->select('name', 'id')
            ->orderBy('id')
            ->get();
    }

    public function getGenderLeaveTypeByBranch($branchId, $gender, $select = ['*'])
    {
        return LeaveType::select($select)
            ->where('is_active', 1)
            ->where('branch_id', $branchId)
            ->where('gender', $gender)
            ->orWhere('gender', LeaveGenderEnum::all->value)
            ->get();
    }

    public function getEmployeePaidLeaveTypes($employeeId)
    {
        $employee = User::select('id', 'gender', 'branch_id')->where('id', $employeeId)->first();

        $gender = $employee->gender;
        return LeaveType::where('branch_id', $employee->branch_id)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                    ->orWhere('gender', LeaveGenderEnum::all->value);
            })
            ->select('name', 'id')
            ->orderBy('id')
            ->get();
    }
}

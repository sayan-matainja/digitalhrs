<?php

namespace App\Repositories;

use App\Helpers\AppHelper;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\BiometricAttendanceLog;
use App\Models\User;
use Carbon\Carbon;

class AttendanceLogRepository
{

    public function getAll($filterData)
    {
        return AttendanceLog::with(['user'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })
            ->when(isset($filterData['branch_id']), function($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('branch_id', $filterData['branch_id']);
                });
            })
            ->when(isset($filterData['department_id']), function($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('department_id', $filterData['department_id']);
                });
            })
            ->when(isset($filterData['employee_id']), function($query) use ($filterData) {
                $query->where('employee_id', $filterData['employee_id']);
            })
            ->paginate(AttendanceLog::RECORDS_PER_PAGE);
    }

    public function getAllBiometricLogs($filterData)
    {
        return BiometricAttendanceLog::with(['user'])
            ->whereHas('user', function ($query) {
                $query->where('is_active', 1);
            })
            ->when(isset($filterData['branch_id']), function ($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('branch_id', $filterData['branch_id']);
                });
            })
            ->when(isset($filterData['department_id']), function ($query) use ($filterData) {
                $query->whereHas('user', function ($query) use ($filterData) {
                    $query->where('department_id', $filterData['department_id']);
                });
            })
            ->when(isset($filterData['employee_id']), function ($query) use ($filterData) {
                $query->where('employee_id', $filterData['employee_id']);
            })
            ->paginate(BiometricAttendanceLog::RECORDS_PER_PAGE);
    }

    public function find($id,$select=['*'])
    {
        return AttendanceLog::select($select)->where('id',$id)->first();
    }

    public function findByEmployeeId($employeeId)
    {
        return AttendanceLog::where('employee_id',$employeeId)->first();
    }

    public function delete(AttendanceLog $attendanceLog)
    {
        return $attendanceLog->delete();
    }

    public function store($validatedData)
    {
        return AttendanceLog::create($validatedData)->fresh();
    }

    public function updateAttendanceLog($attendanceLogDetail,$validatedData)
    {

        $attendanceLogDetail->update($validatedData);
        $attendanceLogDetail->touch();
        return $attendanceLogDetail;
    }
}

<?php

namespace App\Repositories;

use App\Models\EmployeeLeaveType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmployeeLeaveTypeRepository
{
    public function getAll($select=['*'], $employeeId='')
    {
        $leaveTypeData = EmployeeLeaveType::select($select);
        if(!empty($employeeId)){
            $leaveTypeData = $leaveTypeData->where('employee_id', $employeeId)
            ->orderBy('leave_type_id');
        }
        return $leaveTypeData->get();
    }

    public function getByEmployeeId($employeeId, $leaveTypeId)
    {
        return EmployeeLeaveType::where('employee_id',$employeeId)->where('leave_type_id',$leaveTypeId)->first();
    }



    public function store($validatedData)
    {
        return EmployeeLeaveType::create($validatedData)->fresh();
    }

    public function update($leaveTypeDetail, $validatedData)
    {
        return $leaveTypeDetail->update($validatedData);
    }

    public function delete($leaveTypeDetail)
    {
        return $leaveTypeDetail->delete();
    }
 public function deleteByEmployee($employeeId)
    {
        return  EmployeeLeaveType::where('employee_id',$employeeId)->delete();
    }



    public function find($id)
    {
        return EmployeeLeaveType::where('id', $id)->firstorFail();
    }

    public function findByLeaveType($employeeId, $leaveTypeId)
    {
        return EmployeeLeaveType::where([['employee_id', $employeeId],['leave_type_id', $leaveTypeId]])->first();
    }


}

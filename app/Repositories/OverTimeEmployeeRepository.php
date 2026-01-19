<?php

namespace App\Repositories;

use App\Models\OverTimeEmployee;
use App\Models\SalaryGroupEmployee;

class OverTimeEmployeeRepository
{
    public function assignOverTimeToEmployees($overTimeDetail,$validatedEmployeeData)
    {
        return $overTimeDetail->otEmployees()->createMany($validatedEmployeeData);
    }

    public function updateOverTimeEmployee($overTimeDetail,$validatedEmployeeData)
    {
       $this->removeAssignedEmployeeFromOverTime($overTimeDetail);
       return $overTimeDetail->otEmployees()->createMany($validatedEmployeeData);

    }

    public function removeAssignedEmployeeFromOverTime($overTimeDetail)
    {
        return $overTimeDetail->otEmployees()->delete();
    }

    public function getOverTimeByEmployeeId($employeeId)
    {
        return OverTimeEmployee::where('employee_id',$employeeId)->first();
    }

}

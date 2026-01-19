<?php

namespace App\Repositories;

use App\Models\UnderTimeEmployee;
use App\Models\UnderTimeSetting;

class UnderTimeEmployeeRepository
{
    public function assignUnderTimeToEmployees($underTimeDetail,$validatedEmployeeData)
    {
        return $underTimeDetail->utEmployees()->createMany($validatedEmployeeData);
    }

    public function updateUnderTimeEmployee($underTimeDetail,$validatedEmployeeData)
    {
        $this->removeAssignedEmployeeFromUnderTime($underTimeDetail);
        return $underTimeDetail->utEmployees()->createMany($validatedEmployeeData);

    }

    public function removeAssignedEmployeeFromUnderTime($underTimeDetail)
    {
        return $underTimeDetail->utEmployees()->delete();
    }

    public function getUnderTimeByEmployeeId($employeeId)
    {
        return UnderTimeEmployee::where('employee_id',$employeeId)->first();
    }

}

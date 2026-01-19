<?php

namespace App\Repositories;

use App\Models\EmployeeAccount;

class UserAccountRepository
{
    public function store($validatedData)
    {
        return EmployeeAccount::create($validatedData)->fresh();
    }

    public function createOrUpdate($userDetail,$validatedData)
    {
        $account = $userDetail->accountDetail;
        if ($account) {
            $account->update($validatedData);
        } else {
            $userDetail->accountDetail()->create($validatedData);
        }
    }

    public function findAccountDetailById($id,$select=['*'],$with=[])
    {
        return EmployeeAccount::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function findAccountDetailByEmployeeId($userId,$select=['*'],$with=[])
    {
        return EmployeeAccount::select($select)
            ->with($with)
            ->where('user_id',$userId)
            ->first();
    }

    public function updateEmployeeSalaryCycle($employeeAccountDetail,$changeCycleToData)
    {
        $employeeAccountDetail->update([
           'salary_cycle' => $changeCycleToData
        ]);
        return $employeeAccountDetail->fresh();
    }

    public function toggleAllowGeneratePayrollStatus($employeeAccountDetail)
    {
        return $employeeAccountDetail->update([
           'allow_generate_payroll' => !$employeeAccountDetail->allow_generate_payroll
        ]);
    }

    public function updateEmployeeSalary($employeeAccountDetail,$revisedSalaryAmount)
    {
        return $employeeAccountDetail->update([
           'salary' => $revisedSalaryAmount
        ]);
    }

}

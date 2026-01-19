<?php

namespace App\Rules;

use App\Helpers\AppHelper;
use App\Models\EmployeeSalary;
use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class AdvanceSalaryAmountRule implements Rule
{
    public $maxAllowedAmount = '';
    protected $annualSalary = 0;

    public function passes($attribute, $value)
    {
        $employee = EmployeeSalary::query()->select('annual_salary')->where('employee_id',getAuthUserCode())->first();

        $this->annualSalary = $employee->annual_salary ?? 0;
        if ($this->annualSalary === 0) {
            return false;
        }
        $employeeSalary = $this->annualSalary / 12 ;
        $limitInPercent = AppHelper::getMaxAllowedAdvanceSalaryLimit();
        $maxAdvanceAllowed = ($limitInPercent / 100) * $employeeSalary;

        return $value <= $maxAdvanceAllowed;
    }


    public function message()
    {
        if ($this->annualSalary === 0) {
            return 'Unable to process advance salary request because your annual salary is not set.';
        }

        $limitInPercent = AppHelper::getMaxAllowedAdvanceSalaryLimit();
        return 'The advance salary amount cannot exceed ' . $limitInPercent . '% of your salary.';
    }
}

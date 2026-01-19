<?php

namespace App\Repositories;


use App\Models\EmployeePayslipDetail;

class EmployeePayslipDetailRepository
{

    public function getAll($payslipId)
    {
        return EmployeePayslipDetail::select('employee_payslip_details.salary_component_id','employee_payslip_details.amount','salary_components.name','salary_components.component_type')
            ->leftJoin('salary_components','employee_payslip_details.salary_component_id','salary_components.id')
            ->where('employee_payslip_details.employee_payslip_id',$payslipId)
            ->get();
    }

    public function find($payslipId, $salaryComponentId){
        return EmployeePayslipDetail::where('employee_payslip_id',$payslipId)->where('salary_component_id',$salaryComponentId)
            ->first();
    }

    public function store($validatedData)
    {
        return EmployeePayslipDetail::create($validatedData)->fresh();
    }

    public function update($payslipDetail,$validatedData)
    {
        $payslipDetail->update($validatedData);
        return $payslipDetail->fresh();
    }
    public function deleteByPayslipId($payslipId){
        return EmployeePayslipDetail::where('employee_payslip_id',$payslipId)->delete();
    }

}

<?php

namespace App\Repositories;


use App\Models\EmployeePayslipAdditionalDetail;


class EmployeePayslipAdditionalRepository
{

    public function getAll($payslipId)
    {
        return EmployeePayslipAdditionalDetail::select('employee_payslip_additional_details.salary_component_id','employee_payslip_additional_details.amount','salary_components.name','salary_components.component_type')
            ->leftJoin('salary_components','employee_payslip_additional_details.salary_component_id','salary_components.id')
            ->where('employee_payslip_additional_details.employee_payslip_id',$payslipId)
            ->get();
    }

    public function getAdditionalComponents($payslipId)
    {
        return EmployeePayslipAdditionalDetail::select('employee_payslip_additional_details.salary_component_id','employee_payslip_additional_details.amount','salary_components.name','salary_components.component_type')
            ->leftJoin('salary_components','employee_payslip_additional_details.salary_component_id','salary_components.id')
            ->where('employee_payslip_additional_details.employee_payslip_id',$payslipId)
            ->where('salary_components.apply_for_all',1)
            ->get();
    }

    public function find($payslipId, $salaryComponentId){
        return EmployeePayslipAdditionalDetail::where('employee_payslip_id',$payslipId)->where('salary_component_id',$salaryComponentId)
            ->first();
    }

    public function store($validatedData)
    {
        return EmployeePayslipAdditionalDetail::create($validatedData)->fresh();
    }

    public function update($payslipDetail,$validatedData)
    {
        $payslipDetail->update($validatedData);
        return $payslipDetail->fresh();
    }
    public function deleteByPayslipId($payslipId){
        return EmployeePayslipAdditionalDetail::where('employee_payslip_id',$payslipId)->delete();
    }

}

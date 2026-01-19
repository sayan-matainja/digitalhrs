<?php

namespace App\Repositories;


use App\Models\EmployeeSalary;
use App\Models\SalaryReviseHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class EmployeeSalaryRepository
{
    const STATUS = 1;



    public function getAll($select = ['*'])
    {
        return EmployeeSalary::select($select)->get();

    }

    public function getAllEmployeeForPayroll($targetDate,$filterData)
    {

        $targetDate = Carbon::parse($targetDate);
        $endOfMonth = $targetDate->copy()->endOfMonth();

        return EmployeeSalary::select('employee_salaries.employee_id', 'users.marital_status','users.joining_date')
            ->join('users', 'employee_salaries.employee_id', 'users.id')
            ->where('users.is_active', 1)
            ->where('users.status', '=', 'verified')
            ->where('users.joining_date', '<=', $endOfMonth)
            ->when(isset($filterData['branch_id']), function($query) use ($filterData){
                $query->where('users.branch_id', $filterData['branch_id']);
            })
            ->when(isset($filterData['department_id']), function($query) use ($filterData){
                $query->where('users.department_id', $filterData['department_id']);
            })
            ->get();

    }

    public function getAllEmployeeForTaxReport($filterData)
    {
        return EmployeeSalary::select('users.id', 'users.marital_status','users.joining_date')
            ->join('users', 'employee_salaries.employee_id', 'users.id')
            ->where('users.is_active', 1)
            ->where('users.status', '=', 'verified')
            ->when(isset($filterData['branch_id']), function($query) use ($filterData){
                $query->where('users.branch_id', $filterData['branch_id']);
            })
            ->when(isset($filterData['department_id']), function($query) use ($filterData){
                $query->where('users.department_id', $filterData['department_id']);
            })
            ->when(isset($filterData['employee_id']), function($query) use ($filterData){
                $query->where('users.id', $filterData['employee_id']);
            })
            ->get();

    }


    public function getEmployeeSalaryByEmployeeId($employeeId, $select=['*'])
    {
        return EmployeeSalary::select($select)->where('employee_id', $employeeId)->first();
    }

    public function find($id)
    {
        return EmployeeSalary::where('id',$id)->first();
    }


    public function store($validatedData)
    {
        return EmployeeSalary::create($validatedData)->fresh();
    }

    public function update($employeeSalaryDetail,$validatedData)
    {
         return $employeeSalaryDetail->update($validatedData);
    }

    public function delete($employeeSalaryDetail)
    {
        return $employeeSalaryDetail->delete();
    }

    public function getPayrollTypeEmployee($type)
    {

        return EmployeeSalary::select('users.id', 'users.name')
            ->join('users', 'employee_salaries.employee_id', 'users.id')
            ->where('users.is_active', 1)
            ->where('users.status', '=', 'verified')
            ->where('employee_salaries.payroll_type','=', $type)
            ->get();

    }
    public function getDPayrollTypeDepartmentEmployee($departmentIds,$type)
    {

        return EmployeeSalary::select('users.id', 'users.name')
            ->join('users', 'employee_salaries.employee_id', 'users.id')
            ->where('users.is_active', 1)
            ->whereIn('users.department_id', $departmentIds)
            ->where('users.status', '=', 'verified')
            ->where('employee_salaries.payroll_type','=', $type)
            ->get();

    }




}

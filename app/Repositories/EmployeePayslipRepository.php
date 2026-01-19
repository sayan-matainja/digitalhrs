<?php

namespace App\Repositories;

use App\Enum\PayslipStatusEnum;
use App\Models\EmployeePayslip;
use Illuminate\Support\Facades\DB;

class EmployeePayslipRepository
{

    public function getAllEmployeePayslipData($payslipId='')
    {
        $employeePayslipData = EmployeePayslip::select(
            'employee_payslips.employee_id',
            'employee_payslips.paid_on',
            'employee_payslips.status',
            'employee_payslips.remark',
            'employee_payslips.payment_method_id',
            'employee_payslips.salary_cycle',
            'employee_payslips.id',
            'employee_payslips.salary_from',
            'employee_payslips.salary_to',
            'employee_payslips.gross_salary',
            'employee_payslips.tds',
            'employee_payslips.net_salary',
            'employee_payslips.total_days',
            'employee_payslips.present_days',
            'employee_payslips.absent_days',
            'employee_payslips.advance_salary',
            'employee_payslips.tada',
            'employee_payslips.leave_days',
            'employee_payslips.include_tada',
            'employee_payslips.include_advance_salary',
            'employee_payslips.attendance',
            'employee_payslips.absent_paid',
            'employee_payslips.approved_paid_leaves',
            'employee_payslips.absent_deduction',
            'employee_payslips.holidays',
            'employee_payslips.weekends',
            'employee_payslips.paid_leave',
            'employee_payslips.unpaid_leave',
            'employee_payslips.overtime',
            'employee_payslips.undertime',
            'employee_payslips.ssf_deduction',
            'employee_payslips.ssf_contribution',
            'employee_payslips.bonus',
            'employee_payslips.working_hours',
            'employee_payslips.worked_hours',
            'employee_payslips.overtime_hours',
            'employee_payslips.undertime_hours',
            'employee_payslips.hour_rate',
            'employee_payslips.loan_amount',
            'users.name as employee_name',
            'users.avatar as employee_avatar',
            'users.email as employee_email',
            'users.marital_status',
            'users.joining_date as joining_date',
            'posts.post_name as designation',
            'users.employee_code',
            'employee_payslips.pf_deduction',
            'employee_payslips.pf_contribution',
            DB::raw('CAST(users.phone AS UNSIGNED) AS employee_phone'),
            'users.marital_status as marital_status',
            'companies.name as company_name',
            'companies.phone as company_phone',
            'companies.logo as company_logo',
            'companies.address as company_address',
            'companies.email as company_email',
            'departments.dept_name as department',
            'over_time_settings.is_active as ot_status',
            DB::raw("
                    CASE
                        WHEN employee_payslips.salary_to < salary_revise_histories.date_from
                        THEN salary_revise_histories.base_monthly_salary
                        ELSE employee_salaries.monthly_basic_salary
                    END as monthly_basic_salary
                "),
            DB::raw("
                CASE
                    WHEN employee_payslips.salary_to < salary_revise_histories.date_from
                    THEN salary_revise_histories.base_weekly_salary
                    ELSE employee_salaries.weekly_basic_salary
                END as weekly_basic_salary
            "),
            DB::raw("
                CASE
                    WHEN employee_payslips.salary_to < salary_revise_histories.date_from
                    THEN salary_revise_histories.base_monthly_allowance
                    ELSE employee_salaries.monthly_fixed_allowance
                END as monthly_fixed_allowance
            "),
            DB::raw("
                CASE
                    WHEN employee_payslips.salary_to < salary_revise_histories.date_from
                    THEN salary_revise_histories.base_weekly_allowance
                    ELSE employee_salaries.weekly_fixed_allowance
                END as weekly_fixed_allowance
            ")
        )
            ->leftJoin('users','employee_payslips.employee_id','users.id')
            ->leftJoin('companies','users.company_id','companies.id')
            ->leftJoin('departments','users.department_id','departments.id')
            ->leftJoin('employee_salaries','employee_payslips.employee_id','employee_salaries.employee_id')
            ->join('posts','users.post_id','posts.id')
            ->leftJoin('over_time_employees','users.id','over_time_employees.employee_id')
            ->leftJoin('over_time_settings','over_time_employees.over_time_setting_id','over_time_settings.id')
            ->leftJoin('salary_revise_histories', 'employee_payslips.employee_id', '=', 'salary_revise_histories.employee_id');


        if(!empty($payslipId)){
            $employeePayslipData = $employeePayslipData->where('employee_payslips.id',$payslipId)->first();
        }else{
            $employeePayslipData = $employeePayslipData->get();
        }

        return $employeePayslipData;

    }

    public function getEmployeePayslipData($employeeId, $fromDate, $toData)
    {
        return EmployeePayslip::where('employee_id',$employeeId)->where('salary_from',$fromDate)->where('salary_to',$toData)->first();
    }

    public function getEmployeePayslipDataByFiscalYear($employeeId, $fromDate, $toData)
    {
        return EmployeePayslip::select('tds','salary_from')
            ->where('employee_id',$employeeId)
            ->whereDate('salary_from','>=',$fromDate)
            ->whereDate('salary_to','<=',$toData)
            ->where('status',PayslipStatusEnum::paid->value)
            ->where('salary_cycle','=','monthly')
            ->orderBy('salary_from')
            ->get();
    }

    public function getEmployeePayslipSummary($employeeId, $fromDate, $toData, $isBsEnabled, $filterData = [])
    {
        return EmployeePayslip::select(
            'employee_payslips.id',
            'employee_payslips.status',
            'employee_payslips.salary_from',
            'employee_payslips.salary_to',
            'employee_payslips.salary_cycle',
            'employee_payslips.paid_on',
            'employee_payslips.net_salary',
            'employee_payslips.include_tada',
            'employee_payslips.include_advance_salary',
            'employee_payslips.loan_amount',
            'users.name as employee_name',
            'payment_methods.name as paid_by',
                DB::raw("
                        CASE
                            WHEN employee_payslips.salary_to < salary_revise_histories.date_from
                            THEN salary_revise_histories.base_monthly_salary
                            ELSE employee_salaries.monthly_basic_salary
                        END as monthly_basic_salary
                    "),
                DB::raw("
                    CASE
                        WHEN employee_payslips.salary_to < salary_revise_histories.date_from
                        THEN salary_revise_histories.base_monthly_allowance
                        ELSE employee_salaries.monthly_fixed_allowance
                    END as monthly_fixed_allowance
                "),
                DB::raw("
                    CASE
                        WHEN employee_payslips.salary_to < salary_revise_histories.date_from
                        THEN salary_revise_histories.base_salary
                        ELSE employee_salaries.annual_salary
                    END as annual_salary
                    ")
            )
            ->leftJoin('users', 'employee_payslips.employee_id', 'users.id')
            ->leftJoin('employee_salaries', 'users.id', 'employee_salaries.employee_id')
            ->leftJoin('employee_accounts','users.id','employee_accounts.user_id')
            ->leftJoin('payment_methods', 'employee_payslips.payment_method_id', 'payment_methods.id')
            ->leftJoin('salary_revise_histories', 'employee_payslips.employee_id', '=', 'salary_revise_histories.employee_id')
            ->where('employee_payslips.employee_id', $employeeId)
            ->where('employee_payslips.salary_from', $fromDate)
            ->where('employee_payslips.salary_to', $toData)
            ->where('employee_payslips.is_bs_enabled', $isBsEnabled)
            ->when(isset($filterData['include_tada']), function ($query) use ($filterData) {
                $query->where('employee_payslips.include_tada', $filterData['include_tada']);
            }) ->when(isset($filterData['include_advance_salary']), function ($query) use ($filterData) {
                $query->where('employee_payslips.include_advance_salary', $filterData['include_advance_salary']);
            }) ->when(isset($filterData['attendance']), function ($query) use ($filterData) {
                $query->where('employee_payslips.attendance', $filterData['attendance']);
            })
            ->when(isset($filterData['salary_cycle']), function ($query) use ($filterData) {
                $query->where('employee_accounts.salary_cycle', $filterData['salary_cycle']);
            })
            ->first();
    }


    public function getEmployeePayslipList($employeeId, $startDate, $endDate, $isBsEnabled)
    {
        return EmployeePayslip::select(
            'employee_payslips.id',
            'employee_payslips.salary_from',
            'employee_payslips.salary_to',
            'employee_payslips.net_salary',
            'employee_payslips.total_days',
            'employee_payslips.present_days',
            'employee_payslips.absent_days',
            'employee_payslips.leave_days',
            'employee_payslips.holidays',
            'employee_payslips.weekends',
            'employee_payslips.salary_cycle',
            'employee_payslips.status',
            'employee_payslips.loan_amount',
        )
            ->where('employee_payslips.employee_id', $employeeId)
            ->where('employee_payslips.status', PayslipStatusEnum::paid->value)
            ->where('employee_payslips.is_bs_enabled', $isBsEnabled)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('employee_payslips.salary_from', [$startDate, $endDate])
                    ->orWhereBetween('employee_payslips.salary_to', [$startDate, $endDate]);
            })

            ->get();
    }

    public function getPaidEmployeePayslipList($employeeId, $isBsEnabled)
    {
        return EmployeePayslip::select(
            'employee_payslips.id',
            'employee_payslips.salary_from',
            'employee_payslips.salary_to',
            'employee_payslips.net_salary',
            'employee_payslips.total_days',
            'employee_payslips.present_days',
            'employee_payslips.absent_days',
            'employee_payslips.leave_days',
            'employee_payslips.holidays',
            'employee_payslips.weekends',
            'employee_payslips.salary_cycle',
            'employee_payslips.status',
            'employee_payslips.loan_amount',
        )
            ->where('employee_payslips.employee_id', $employeeId)
            ->where('employee_payslips.status', PayslipStatusEnum::paid->value)
            ->where('employee_payslips.is_bs_enabled', $isBsEnabled)
            ->orderBy('employee_payslips.salary_to', 'desc')
            ->get()->take(12);
    }

    public function getEmployeeCurrentPayslipList($firstDay, $lastDay, $isBsEnabled)
    {
        $branchId = null;
        $authUserId = null;
        if(auth()->user()){
            $branchId = auth()->user()->branch_id;
            $authUserId = auth()->user()->id;
        }

        return EmployeePayslip::select(
            'employee_payslips.id',
            'employee_payslips.employee_id',
            'employee_payslips.salary_from',
            'employee_payslips.salary_to',
            'employee_payslips.net_salary',
            'employee_payslips.total_days',
            'employee_payslips.present_days',
            'employee_payslips.absent_days',
            'employee_payslips.leave_days',
            'employee_payslips.holidays',
            'employee_payslips.weekends',
            'employee_payslips.salary_cycle',
            'employee_payslips.status',
            'employee_payslips.working_hours',
            'employee_payslips.worked_hours',
            'payment_methods.name as paid_by',
            'users.name as employee_name',
            'employee_payslips.paid_on',
            'employee_payslips.loan_amount',
        )
            ->leftJoin('users', 'employee_payslips.employee_id', 'users.id')
            ->leftJoin('payment_methods', 'employee_payslips.payment_method_id', 'payment_methods.id')
            ->where(function($query) use ($firstDay, $lastDay) {
                $query->whereBetween('employee_payslips.salary_from', [$firstDay, $lastDay])
                    ->orWhereBetween('employee_payslips.salary_to', [$firstDay, $lastDay]);
            })
            ->where('employee_payslips.is_bs_enabled', $isBsEnabled)
            ->when(isset($branchId) && ($authUserId != 1), function ($query) use ($branchId) {
                $query->where('users.branch_id', $branchId);
            })
            ->orderBy('employee_payslips.status')
            ->get();
    }
    public function find($payslipId)
    {
        return EmployeePayslip::where('id', $payslipId)->first();
    }

    public function store($validatedData)
    {
        return EmployeePayslip::create($validatedData)->fresh();
    }

    public function update($payslipDetail,$validatedData)
    {
        $payslipDetail->update($validatedData);
        return $payslipDetail->fresh();
    }

    public function changeEmployeePayslipStatus($payslipDetail,$payslipStatus)
    {
         $payslipDetail->update([
            'status' => $payslipStatus
        ]);
         return $payslipDetail;
    }

    public function delete($payslipDetail)
    {
        return $payslipDetail->delete();
    }

    public function getEmployeeSsfList($employeeId, $startDate, $endDate)
    {
        return EmployeePayslip::select(
            'employee_payslips.id',
            'employee_payslips.salary_from',
            'employee_payslips.salary_to',
            'employee_payslips.ssf_deduction',
            'employee_payslips.ssf_contribution',
        )
            ->where('employee_payslips.ssf_deduction','!=', 0)
            ->where('employee_payslips.employee_id', $employeeId)
            ->where('employee_payslips.status', PayslipStatusEnum::paid->value)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('employee_payslips.salary_from', [$startDate, $endDate])
                    ->orWhereBetween('employee_payslips.salary_to', [$startDate, $endDate]);
            })
            ->get();
    }

    public function getRecentEmployeeSsfList($employeeId)
    {
        return EmployeePayslip::select(
            'employee_payslips.id',
            'employee_payslips.salary_from',
            'employee_payslips.salary_to',
            'employee_payslips.ssf_deduction',
            'employee_payslips.ssf_contribution',
        )
            ->where('employee_payslips.ssf_deduction','!=', 0)
            ->where('employee_payslips.employee_id', $employeeId)
            ->where('employee_payslips.status', PayslipStatusEnum::paid->value)
            ->orderBy('employee_payslips.salary_to', 'desc')
            ->get()->take(12);
    }
}

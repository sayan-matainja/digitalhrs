<?php

namespace App\Helpers;

use App\Models\EmployeeLeaveType;
use App\Models\LeaveRequestMaster;
use App\Models\LeaveType;
use App\Models\OverTimeEmployee;
use App\Models\OverTimeSetting;
use App\Models\SalaryGroup;
use App\Models\SalaryTDS;
use App\Models\UnderTimeSetting;
use Faker\Core\Number;

class PayrollHelper
{

    public static function salaryTDSCalculator($maritalStatus, $annualIncome): array
    {
        $count = 1;
        $taxAmount = 0;
        $tax1 = 0;

        $taxSlabs = SalaryTDS::where([['marital_status', $maritalStatus], ['annual_salary_from', '<', $annualIncome]])
            ->orderBy('tds_in_percent', 'desc')
            ->get();


        $slabCount = count($taxSlabs);
        if ($slabCount > 0) {
            foreach ($taxSlabs as $key => $taxSlab) {
                if ($count) {
                    $slab = $annualIncome - $taxSlab['annual_salary_from'];
                    if ($key == $slabCount-1) {
                        $tax1 = $slab * ($taxSlab['tds_in_percent'] / 100);
                    }
                    $taxAmount = $slab * ($taxSlab['tds_in_percent'] / 100);

                    $count = 0;
                } else {

                    $slab = $taxSlab['annual_salary_to'] - $taxSlab['annual_salary_from'];

                    if ($key == $slabCount-1) {
                        $tax1 = $slab * ($taxSlab['tds_in_percent'] / 100);
                    }
                    $taxAmount += $slab * ($taxSlab['tds_in_percent'] / 100);
                }


            }
        }

        $monthlyTax = $taxAmount / 12;
        $weeklyTax = $taxAmount / 52;

        return [
            'sst' => $tax1,
            'total_tax' => round($taxAmount, 2),
            'monthly_tax' => round($monthlyTax, 2),
            'weekly_tax' => round($weeklyTax, 2),
                    //'income_tax' => $taxAmount - $tax1,
        ];

    }

    public static function overTimeCalculator($employeeId, $grossSalary): array
    {

        $overTimeSetting = OverTimeEmployee::select('over_time_settings.*')
            ->leftJoin('over_time_settings', function ($join) {
                $join->on('over_time_employees.over_time_setting_id', '=', 'over_time_settings.id')
                    ->where('over_time_settings.is_active',1);
            })
            ->where('over_time_employees.employee_id', $employeeId)
            ->first();

        if(isset($overTimeSetting)){

            if($overTimeSetting->pay_type == 0){
                $rate = ($overTimeSetting->pay_percent /100) * $grossSalary;
            }else{
                $rate = $overTimeSetting->overtime_pay_rate;
            }
            $weekly_limit = $overTimeSetting->max_weekly_ot_hours * 60;
            $monthly_limit = $overTimeSetting->max_monthly_ot_hours * 60;
        }

        return [
            'weekly_limit' => $weekly_limit ?? 0,
            'monthly_limit' => $monthly_limit ?? 0,
            'hourly_rate' => isset($rate) ? round($rate,2) : 0,
        ];
    }

    public static function underTimeCalculator($grossSalary): float|int
    {
       $underTimeSetting = UnderTimeSetting::where('is_active',1)->first();
        if(isset($underTimeSetting)){
            if($underTimeSetting->penalty_type == 0){
                $rate = ($underTimeSetting->penalty_percent /100) * $grossSalary;
            }else{
                $rate = $underTimeSetting->ut_penalty_rate;
            }
        }

        return isset($rate) ? round($rate,2) : 0;


    }

    public static function getLeaveData($employeeId, $firstDay, $lastDay)
    {
        $leaveData = EmployeeLeaveType::select('leave_type_id as id','days')->where('employee_id',$employeeId)->get();

        if(count($leaveData) == 0){

            $leaveData = LeaveType::select('id','leave_allocated as days')->whereNotNull('leave_allocated')->get();

        }

        $leaveTakenByType = LeaveRequestMaster::leftJoin('leave_types','leave_requests_master.leave_type_id','leave_types.id')
            ->where('leave_requests_master.requested_by', $employeeId)
            ->where('leave_requests_master.leave_from', '>=', $firstDay)
            ->where('leave_requests_master.leave_to', '<=', $lastDay)
            ->where('leave_requests_master.status', '=', 'approved')
            ->where('leave_requests_master.early_exit', '=', 0)
            ->selectRaw(
                'CASE
                    WHEN leave_types.leave_allocated IS NULL THEN "unpaid"
                    ELSE "paid"
                END AS leave_type,
                SUM(leave_requests_master.no_of_days) as total_days
            ')
            ->groupBy('leave_requests_master.leave_type_id')
            ->get();


        return [
            'leaveData'=>$leaveData,
            'leaveTakenByType'=>$leaveTakenByType
        ];

    }

}

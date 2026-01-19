<?php

namespace App\Resources\Payroll\Payslip;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Models\Company;
use Illuminate\Http\Resources\Json\JsonResource;
use MilanTarami\NumberToWordsConverter\Services\NumberToWords;

class PayslipResource extends JsonResource
{
    public function toArray($request): array
    {
        $numberToWords = new NumberToWords();

        if ($this->salary_cycle == 'weekly') {
            $payslipTitle = 'Payslip from ' . AttendanceHelper::payslipDate($this->salary_from) . 'to ' . AttendanceHelper::payslipDate($this->salary_to);

            $basicSalary = $this->weekly_basic_salary;
            $fixedAllowance = $this->weekly_fixed_allowance;
        } else {

            $payslipTitle = 'Payslip for the Month of ' . AttendanceHelper::payslipDuration($this->salary_from,$this->salary_to);
            $basicSalary = $this->monthly_basic_salary;
            $fixedAllowance = $this->monthly_fixed_allowance;
        }

        return [
            "company_logo"=> isset($this->company_logo) ? asset(Company::UPLOAD_PATH.$this->company_logo) : asset('assets/images/img.png'),
            "payslip_title"=> $payslipTitle,
            "company_name"=> $this->company_name,
            "company_address"=> $this->company_address,
            "employee_name"=> $this->employee_name,
            "designation"=> $this->designation,
            "employee_id"=> $this->employee_id,
            "joining_date"=> isset($this->joining_date) ? AttendanceHelper::payslipDate($this->joining_date):'',
            "total_days"=> $this->total_days,
            "present_days"=> $this->present_days,
            "absent_days"=> $this->absent_days,
            "leave_days"=> $this->leave_days,
            "holidays"=> $this->holidays,
            "weekends"=> $this->weekends,
            "paid_leave"=> $this->paid_leave,
            "unpaid_leave"=> $this->unpaid_leave,
            "basic_salary"=> $basicSalary,
            "fixed_allowance"=> $fixedAllowance,
            "gross_salary"=> $this->gross_salary,
            "tds"=> $this->tds,
            "tada"=> $this->tada,
            "advance_salary"=> $this->advance_salary,
            "absent_deduction"=> $this->absent_deduction,
            "overtime"=> $this->overtime,
            "undertime"=> $this->undertime,
            "net_salary"=> $this->net_salary,
            "net_salary_figure" => $numberToWords->get($this->net_salary),
            "employee_code" => $this->employee_code,
            'pf_deduction'=>$this->pf_deduction,
            'ssf_deduction'=>$this->ssf_deduction,
        ];

    }


}

<?php

namespace App\Resources\Payroll\Payslip;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Transformers\AdvanceSalaryDocumentTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id ?? 0,
            'payslip_id' => '#Slip'.$this->id ?? '',
            'salary_from' => AttendanceHelper::payslipDate($this->salary_from),
            'salary_to' => AttendanceHelper::payslipDate($this->salary_to),
            'duration' => AttendanceHelper::payslipDuration($this->salary_from, $this->salary_to),
            'net_salary' => $this->net_salary,
            'total_days' => $this->total_days,
            'present_days' => $this->present_days,
            'absent_days' => $this->absent_days,
            'leave_days' => $this->leave_days,
            'holidays' => $this->holidays,
            'weekends' => $this->weekends,
            'salary_cycle' => ucfirst($this->salary_cycle),
        ];
    }


}

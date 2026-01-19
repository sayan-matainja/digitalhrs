<?php

namespace App\Resources\Payroll\Payslip;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Transformers\AdvanceSalaryDocumentTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class SSFHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'month' => AttendanceHelper::payslipDuration($this->salary_from, $this->salary_to),
            'office_contribution' => $this->ssf_contribution,
            'salary_contribution' => $this->ssf_deduction,
        ];
    }


}

<?php

namespace App\Resources\Loan;

use App\Enum\LoanStatusEnum;
use App\Helpers\AppHelper;
use App\Models\Loan;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanSettlementRequestResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'loan_id' => $this->loan->id,
            'loan_title_id' => $this->loan->loan_id ?? '',
            'requested_date' => isset($this->created_at) ?AppHelper::formatDateForView($this->created_at) : '',
            'settlement_type' => ucfirst($this->settlement_type),
            'settlement_method' => ucfirst($this->settlement_method),
            'amount' => isset($this->amount) ? bcdiv($this->amount,1,2) : 0,
            'currency' => AppHelper::getCompanyPaymentCurrencySymbol(),
            'status' => ucfirst($this->status),
            'reason' => removeHtmlTags($this->reason ?? ''),
            'remarks' => removeHtmlTags($this->remarks ?? ''),
            'approved_by' => isset($this->approved_by ) ? $this->approvedBy->name : ($this->status == LoanStatusEnum::approve->value ? 'Admin' : ''),

        ];
    }
}

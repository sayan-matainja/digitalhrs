<?php

namespace App\Resources\Loan;

use App\Enum\LoanStatusEnum;
use App\Helpers\AppHelper;
use App\Models\Loan;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class LoanResource extends JsonResource
{
    public function toArray($request)
    {
        $nextRepayment = $this->loanRepayment
            ->where('status', 'upcoming')
            ->sortByDesc('payment_date')
            ->first();

        $lastRepayment = $this->loanRepayment->sortByDesc('payment_date')->first();
        return [
            'id' => $this->id,
            'loan_id' => ucfirst($this->loan_id),
            'loan_type' => ucfirst($this->loanType->name ?? ''),
            'loan_type_id' => $this->loan_type_id,
            'total_expense' => $this->loan_amount ?? 0,
            'status' => ucfirst($this->status),
            'remarks' => removeHtmlTags($this->remarks) ?? '',
            'description' => removeHtmlTags($this->description) ?? '',
            'application_date' => isset($this->application_date) ? AppHelper::formatDateForView($this->application_date) : '',
            'repayment_from' => isset($this->repayment_from) ? AppHelper::formatDateForView($this->repayment_from) : '',
            'monthly_installment' => $this->monthly_installment ?? 0,
            'repayment_amount' => $this->repayment_amount ?? 0,
            'issue_date' => isset($this->issue_date) ?AppHelper::formatDateForView($this->issue_date) : '',
            'loan_purpose' => ucfirst($this->loan_purpose ?? ''),
            'interest_rate' => $this->loanType->interest_rate ?? 0,
            'interest_type' => ucfirst($this->loanType->interest_type ?? ''),
            'term' => $this->loanType->term ?? 0,
            'currency' => AppHelper::getCompanyPaymentCurrencySymbol(),
            'next_interest_amount' => $nextRepayment->interest_amount ?? 0,
            'loan_due_at' => $lastRepayment && $lastRepayment->payment_date ? AppHelper::formatDateForView($lastRepayment->payment_date) : '',
            'attachment' => isset($this->attachment) ? asset(Loan::UPLOAD_PATH . $this->attachment) : '',
            'updated_by' => isset($this->updated_by) ? $this->updatedBy->name : (in_array($this->status,[LoanStatusEnum::approve->value, LoanStatusEnum::settled->value]) ? 'Admin' : ''),
            'payment_method' => isset($this->paymentMethod->name) ? ucfirst($this->paymentMethod->name) : '',
        ];
    }
}

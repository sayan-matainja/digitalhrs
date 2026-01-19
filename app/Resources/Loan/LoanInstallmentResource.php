<?php

namespace App\Resources\Loan;

use App\Enum\LoanRepaymentStatusEnum;
use App\Helpers\AppHelper;
use App\Models\LoanRepayment;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class LoanInstallmentResource extends JsonResource
{
    public function toArray($request)
    {
        $nextPayment = LoanRepayment::select(['id','payment_date'])
            ->where('employee_id', getAuthUserCode())
            ->where('status','=',LoanRepaymentStatusEnum::upcoming->value)
            ->orderBy('payment_date')
            ->first();

           $outStanding = LoanRepayment::whereIn('status', [LoanRepaymentStatusEnum::active->value, LoanRepaymentStatusEnum::upcoming->value])
               ->where('employee_id', getAuthUserCode())
               ->get()
               ->sum(function ($repayment) {
                   return ($repayment->principal_amount ?? 0) + ($repayment->interest_amount ?? 0);
               });

        $due = AppHelper::getLoanInstallmentDue();

        return [
            'id' => $this->id,
            'loan_id'=>$this->loan_id,
            'emi' => round($this->principal_amount + $this->interest_amount,2),
            'outstanding_amount' => round($outStanding,2),
            'status' => ucfirst($this->status),
            'currency' => AppHelper::getCompanyPaymentCurrencySymbol(),
            'days_left' => $due,
            'next_payment_date' => isset($nextPayment->payment_date) ? AppHelper::formatDateForView($nextPayment->payment_date) : '-',
        ];
    }
}

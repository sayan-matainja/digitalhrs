<?php

namespace App\Resources\Loan;

use App\Enum\LoanRepaymentStatusEnum;
use App\Helpers\AppHelper;
use App\Models\LoanRepayment;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class LoanRepaymentResource extends JsonResource
{
    public function toArray($request)
    {
        foreach ($this->sortBy('payment_date') as $repayment) {
            $year = AppHelper::getYearValue($repayment->payment_date);

            $schedule = $repayment->principal_amount + $repayment->interest_amount;
            $interest = $repayment->interest_amount;
            $status = $repayment->status;
            $principalPaid = $repayment->principal_amount;
            $balance = round(max(0, $remainingBalance - $principalPaid), 2);

            $yearlyData[] = [
                'year' => AppHelper::getYearValue($repayment->payment_date),
                'month' => AppHelper::getMonth($repayment->payment_date),
                'schedule' => $schedule,
                'interest' => $interest,
                'principal' => $principalPaid,
                'balance' => $balance,
                'status' => $status,
            ];

            $yearlyData[$year]['totals']['schedule'] += $schedule;
            $yearlyData[$year]['totals']['interest'] += $interest;
            $yearlyData[$year]['totals']['principal'] += $principalPaid;

            $remainingBalance = $balance;
        }

        return [
            'id' => $this->id,
            'loan_id'=>$this->loan_id,
            'principal_amount' => $this->principal_amount,
            'interest_amount' => $this->interest_amount,
            'status' => ucfirst($this->status),
            'days_left' => $due,
            'next_payment_date' => isset($nextPayment->payment_date) ? AppHelper::formatDateForView($nextPayment->payment_date) : '-',
        ];
    }
}

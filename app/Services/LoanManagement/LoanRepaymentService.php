<?php

namespace App\Services\LoanManagement;

use App\Enum\LoanInterestTypeEnum;
use App\Enum\LoanRepaymentStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Repositories\LoanRepaymentRepository;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanRepaymentService
{
    public function __construct(
        protected LoanRepaymentRepository $loanRepaymentRepo, protected LoanTypeService $loanTypeService, protected LoanService $loanService
    ){}


    public function getAllRepaymentsPaginated($filterParameters,$select= ['*'],$with=[])
    {
        return $this->loanRepaymentRepo->getAllRepaymentsPaginated($filterParameters,$select,$with);
    }


    public function getAllRepayments($loanId,$select= ['*'],$with=[])
    {
        return $this->loanRepaymentRepo->getAllRepayments($loanId,$select,$with);
    }


    public function getLoanRepayment($employeeId,$startDate,$endDate,$select= ['*'],$with=[])
    {
        return $this->loanRepaymentRepo->findLoanRepayment($employeeId,$startDate,$endDate,$select,$with);
    }


    public function getRecentLoanInstallment($select=['*'], $with=[])
    {
        return $this->loanRepaymentRepo->getRecentInstallment($select, $with);
    }

     public function getEmployeeRecentInstallment($employeeId, $select=['*'], $with=[])
    {
        return $this->loanRepaymentRepo->getEmployeeRecentInstallment($employeeId, $select, $with);
    }

    public function getPastMonthLoanInstallment($loanRepayment,$select=['*'], $with=[])
    {
        return $this->loanRepaymentRepo->getPastMonthInstallment($loanRepayment,$select, $with);
    }

    public function updateRepayment($repaymentDetail, $validatedData)
    {
        return $this->loanRepaymentRepo->update($repaymentDetail, $validatedData);
    }

    /**
     * @throws Exception
     */
    public function loanSettlement($employeePaySlipDetail,$paidDate)
    {
        $loanRepayment = $this->loanRepaymentRepo->getRepaymentById($employeePaySlipDetail->loan_repayment_id);

        return $this->loanRepaymentRepo->settleRepayment($loanRepayment,$paidDate);
    }


    private function calculateLoanRepaymentSchedule(Loan $loan, LoanRepayment $currentRepayment): void
    {
        $loanType = $loan->loanType;
        $principal = $loan->loan_amount;
        $interestRate = $loanType->interest_rate;
        $interestType = $loanType->interest_type ?? LoanInterestTypeEnum::fixed->value;
        $tenureMonths =  $loanType->term;

        $monthlyRate = $interestRate / 12 / 100;

        $totalPaidPrincipal = LoanRepayment::where('loan_id', $loan->id)
            ->where('payment_date', '<=', $currentRepayment->payment_date)
            ->sum('principal_amount');

        $remainingPrincipal = round($principal - $totalPaidPrincipal, 2);

        if ($remainingPrincipal <= 0) {
            return;
        }

        // Calculate original EMI based on full loan details
        if ($interestType === 'fixed') {
            $tenureYears = $tenureMonths / 12;
            $totalInterest = $principal * $interestRate / 100 * $tenureYears;
            $monthlyInterest = $totalInterest / $tenureMonths;
            $monthlyPrincipal = $principal / $tenureMonths;
            $emi = round($monthlyPrincipal + $monthlyInterest, 2);
        } else {
            $emiNumerator = $principal * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths);
            $emiDenominator = pow(1 + $monthlyRate, $tenureMonths) - 1;
            $emi = $emiDenominator > 0 ? round($emiNumerator / $emiDenominator, 2) : 0;
        }

        $repayments = [];

        $nextPaymentDate = new DateTime($currentRepayment->payment_date);
        $nextPaymentDate->modify('+1 month');

        while ($remainingPrincipal > 0) {
            if ($interestType === 'fixed') {
                $interestAmount = round($monthlyInterest, 2);
                $principalAmount = round($monthlyPrincipal, 2);

                // Adjust for last payment if remaining is less
                if ($remainingPrincipal < $principalAmount) {
                    $principalAmount = $remainingPrincipal;
                }
            } else {
                $interestAmount = round($remainingPrincipal * $monthlyRate, 2);
                $principalAmount = round($emi - $interestAmount, 2);

                if ($principalAmount > $remainingPrincipal) {
                    $principalAmount = $remainingPrincipal;
                }
            }

            if ($principalAmount <= 0 || $interestAmount < 0) {
                break;
            }

            $repayments[] = [
                'loan_id' => $loan->id,
                'employee_id' => $currentRepayment->employee_id,
                'principal_amount' => $principalAmount,
                'interest_amount' => $interestAmount,
                'paid_date' => null,
                'is_paid' => 0,
                'payment_date' => $nextPaymentDate->format('Y-m-d'),
                'status' => 'upcoming',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $remainingPrincipal -= $principalAmount;
            $remainingPrincipal = round($remainingPrincipal, 2);

            $nextPaymentDate->modify('+1 month');


            if ($remainingPrincipal == 0) {
                break;
            }
        }

        Log::info(json_encode($repayments));
        if (!empty($repayments)) {
            DB::table('loan_repayments')->insert($repayments);
        }
    }





}

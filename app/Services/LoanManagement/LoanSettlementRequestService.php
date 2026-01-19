<?php

namespace App\Services\LoanManagement;

use App\Enum\LoanInterestTypeEnum;
use App\Enum\LoanRepaymentStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Helpers\AppHelper;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\LoanRepository;
use App\Repositories\LoanSettlementRequestRepository;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanSettlementRequestService
{
    public function __construct(
        protected LoanSettlementRequestRepository $settlementRequestRepository, protected LoanRepository $loanRepository,
        protected LoanService $loanService, protected LoanRepaymentService $repaymentService, protected EmployeeSalaryRepository $employeeSalaryRepository
    ){}

    public function getSettlementRequests($filterParameters,$select= ['*'],$with=[])
    {
        return $this->settlementRequestRepository->getAllSettlementRequest($filterParameters,$select,$with);
    }

    /**
     * @throws Exception
     */
    public function findSettlementRequestById($id, $select=['*'], $with=[])
    {

        $LoanDetail =  $this->settlementRequestRepository->findSettlementRequestById($id,$select,$with);
        if(!$LoanDetail){
            throw new Exception(__('message.loan_settlement_not_found'),400);
        }
        return $LoanDetail;

    }

    /**
     * @throws Exception
     */
    public function saveLoanSettlementRequest($validatedData)
    {
        $pendingLoan = $this->settlementRequestRepository->checkPendingLoanSettlementRequest($validatedData['employee_id']);
        if($pendingLoan){
            throw new Exception(__('message.loan_settlement_pending_error'),400);
        }

        $salary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($validatedData['employee_id'],['id','monthly_basic_salary']);

        if (!$salary) {
            throw new Exception('You cannot request Loan, your salary is not set.');
        }


        $currentRepayment = $this->repaymentService->getEmployeeRecentInstallment($validatedData['employee_id'],['*'],['loan']);
        if($currentRepayment){
            $monthRequestAmount = $this->settlementRequestRepository->getSettlementRequestAmount($validatedData['employee_id']);

            $requestAmount = (float) $validatedData['amount'];
            $repaymentSettlementAmount = $currentRepayment->settlement_amount ?? 0 + $currentRepayment->principal_amount + $currentRepayment->interest_amount  ;
            $loanAmount = $currentRepayment->loan->loan_amount ?? 0;

            $paidPrincipal =  LoanRepayment::where('loan_id', $validatedData['loan_id'])
                ->where('payment_date', '<', $currentRepayment->payment_date)
                ->sum('principal_amount');

            $remainingPrincipal = $loanAmount - $paidPrincipal;
            if($validatedData['settlement_type'] === 'partial' && ($remainingPrincipal > 0) && ($requestAmount) > $remainingPrincipal){
                throw new Exception('Requested settlement amount is more than remaining principal.');
            }

            if(($repaymentSettlementAmount > 0) && ($repaymentSettlementAmount) >= $loanAmount){
                throw new Exception('your loan is already settled.');
            }


            if ($validatedData['settlement_method'] === 'salary') {

                $basicSalary = (float) $salary->monthly_basic_salary;

                $projectedTotal = $monthRequestAmount + $requestAmount;
                if ($projectedTotal > $basicSalary) {
                    throw new Exception('You cannot request settlement more than your basic salary.');
                }
            }
        }else{
            throw new Exception('Your loan installment for this month is not found.');
        }

        $validatedData['status'] = LoanStatusEnum::pending->value;
        return $this->settlementRequestRepository->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateLoanSettlementRequest($id, $validatedData)
    {
        $loanDetail = $this->findSettlementRequestById($id);

        return $this->settlementRequestRepository->update($loanDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteLoanSettlementRequest($id)
    {

        $loanDetail = $this->findSettlementRequestById($id);

        return $this->settlementRequestRepository->delete($loanDetail);

    }

    /**
     * @throws Exception
     */
    public function changeLoanSettlementRequestStatus($id, $validatedData)
    {
        $loanSettlementDetail = $this->findSettlementRequestById($id);

        $updateData = $validatedData;

        if (isset($validatedData['status']) && $validatedData['status'] === LoanStatusEnum::approve->value) {

            if($loanSettlementDetail->settlement_type == 'partial'){

                if($loanSettlementDetail->settlement_method == 'salary'){

                    $this->partialLoanPaymentBySalary($loanSettlementDetail);
                }else{

                   $this->manualPartialLoanPayment($loanSettlementDetail);
                }

            }else{
                if($loanSettlementDetail->settlement_method == 'salary'){
                   $this->fullLoanPaymentBySalary($loanSettlementDetail);
                }else{

                   $this->manualFullLoanPayment($loanSettlementDetail);
                }
            }

        }


        $updateData['approved_by'] = auth()->user()->id ?? null;
        $this->settlementRequestRepository->update($loanSettlementDetail, $updateData);


        return $loanSettlementDetail->fresh();
    }


    /**
     * @throws Exception
     */
    private function partialLoanPaymentBySalary ($loanSettlementDetail):void
    {
        $month = AppHelper::getCurrentMonthDates();
        $loanRepayment = $this->repaymentService->getLoanRepayment($loanSettlementDetail->employee_id, $month['start_date'], $month['end_date']);
        if($loanRepayment){
            $loanDetail = $this->loanService->findLoanById($loanRepayment->loan_id,['*'],['loanType']);

            $currentInstallment = $loanRepayment->principal_amount + $loanRepayment->interest_amount;
            $settlementAmount = $loanSettlementDetail->amount;

            if ($settlementAmount < $currentInstallment) {
                throw new Exception('Payment amount is less than due amount.');
            }


            $extra = $settlementAmount - $currentInstallment;

            $recalculate = $extra > 0;

            if ($recalculate) {
                $loanRepayment->settlement_amount += $extra;
                $loanRepayment->save();
            }

            if ($recalculate) {
                LoanRepayment::where('loan_id', $loanRepayment->loan_id)
                    ->where('payment_date', '>', $loanRepayment->payment_date)
                    ->delete();

                $this->calculateLoanRepaymentSchedule($loanDetail, $loanRepayment);
            }

        }
    }

    /**
     * @throws Exception
     */
    private function fullLoanPaymentBySalary ($loanSettlementDetail):void
    {
        $month = AppHelper::getCurrentMonthDates();
        $loanRepayment = $this->repaymentService->getLoanRepayment($loanSettlementDetail->employee_id, $month['start_date'], $month['end_date']);
        if($loanRepayment){
            $loanDetail = $this->loanService->findLoanById($loanRepayment->loan_id,['*'],['loanType']);

            $loanAmount = $loanDetail->loan_amount;

            $paidPrincipal =  LoanRepayment::where('loan_id', $loanRepayment->loan_id)
                ->where('payment_date', '<=', $loanRepayment->payment_date)
                ->sum('principal_amount');

            $extraPayment = $loanAmount - $paidPrincipal;

            if ($extraPayment) {
                $settlementAmount = $extraPayment + $loanRepayment->principal_amount +  $loanRepayment->interest_amount;

                $salary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($loanSettlementDetail->employee_id,['id','monthly_basic_salary']);

                if ($settlementAmount > $salary->monthly_basic_salary) {
                    throw new Exception('This settlement request cannot be approved. Loan settlement amount is more than basic Salary.');
                }


                $updateSettlementAmount = $loanRepayment->settlement_amount + $extraPayment;
                $updateData = [
                    'settlement_amount' => $updateSettlementAmount,
                ];

                $this->repaymentService->updateRepayment($loanRepayment, $updateData);


                LoanRepayment::where('loan_id', $loanRepayment->loan_id)
                    ->where('payment_date', '>', $loanRepayment->payment_date)
                    ->delete();

            }

        }
    }

    /**
     * @throws Exception
     */
    private function manualPartialLoanPayment ($loanSettlementDetail):void
    {
        $month = AppHelper::getCurrentMonthDates();
        $loanRepayment = $this->repaymentService->getLoanRepayment($loanSettlementDetail->employee_id, $month['start_date'], $month['end_date']);
        if($loanRepayment){
            $loanDetail = $this->loanService->findLoanById($loanRepayment->loan_id,['*'],['loanType']);

            $currentInstallment = $loanRepayment->principal_amount + $loanRepayment->interest_amount;
            $settlementAmount = $loanSettlementDetail->amount;

            if ($settlementAmount < $currentInstallment) {
                throw new Exception('Payment amount is less than installment amount.');
            }

            $extra = $settlementAmount - $currentInstallment;
            $recalculate = $extra > 0;

            if ($recalculate) {
                $loanRepayment->settlement_amount += $extra;
                $loanRepayment->is_paid = 1;
                $loanRepayment->paid_date = date('Y-m-d');
                $loanRepayment->status = LoanRepaymentStatusEnum::paid->value;
                $loanRepayment->save();
            }

            if ($recalculate) {
                LoanRepayment::where('loan_id', $loanRepayment->loan_id)
                    ->where('payment_date', '>', $loanRepayment->payment_date)
                    ->delete();

                $this->calculateLoanRepaymentSchedule($loanDetail, $loanRepayment);
            }

        }
    }

    /**
     * @throws Exception
     */
    private function manualFullLoanPayment ($loanSettlementDetail):void
    {
        $month = AppHelper::getCurrentMonthDates();
        $loanRepayment = $this->repaymentService->getLoanRepayment($loanSettlementDetail->employee_id, $month['start_date'], $month['end_date']);
        if($loanRepayment){
            $loanDetail = $this->loanService->findLoanById($loanRepayment->loan_id,['*'],['loanType']);

            $loanAmount = $loanDetail->loan_amount;
            Log::info('loan_amount '. $loanAmount);
            $paidPrincipal =  LoanRepayment::where('loan_id', $loanRepayment->loan_id)
                ->where('payment_date', '<=', $loanRepayment->payment_date)
                ->sum('principal_amount');

            Log::info('paidPrincipal '. $paidPrincipal);
            $extraPayment = $loanAmount - $paidPrincipal;
            Log::info('extraPayment = loanAmount - paid principal '. $extraPayment);
            if ($extraPayment) {
                $settlementAmount = $loanSettlementDetail->amount;
                Log::info('settlementAmount '. $settlementAmount);
                $interest = $settlementAmount - $extraPayment - $loanRepayment->principal_amount;
                Log::info('interest '. $interest);
                $requestDate = Carbon::parse($loanSettlementDetail->created_at)->startOfDay();

                $updateSettlementAmount = $loanRepayment->settlement_amount + ($settlementAmount - $interest - $loanRepayment->principal_amount);
                Log::info('updateSettlementAmount '. $updateSettlementAmount);
                $updateData = [
                    'interest_amount' => round($interest, 2),
                    'settlement_amount' => $updateSettlementAmount,
                    'is_paid' => 1,
                    'paid_date' => $requestDate,
                    'status' => LoanRepaymentStatusEnum::paid->value,
                ];

                $this->repaymentService->updateRepayment($loanRepayment, $updateData);


                LoanRepayment::where('loan_id', $loanRepayment->loan_id)
                    ->where('payment_date', '>', $loanRepayment->payment_date)
                    ->delete();

            }

        }
    }

    /**
     * @throws Exception
     */
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
            ->selectRaw('SUM(COALESCE(principal_amount, 0) + COALESCE(settlement_amount, 0)) as total_paid_principal')
            ->value('total_paid_principal');

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

        if (!empty($repayments)) {
            DB::table('loan_repayments')->insert($repayments);
        }
    }






}

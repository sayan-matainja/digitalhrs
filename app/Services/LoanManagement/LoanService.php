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
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanService
{
    public function __construct(
        protected LoanRepository $loanRepo, protected LoanTypeService $loanTypeService, protected EmployeeSalaryRepository $employeeSalaryRepository
    ){}

    public function getAllLoansPaginated($filterParameters,$select= ['*'],$with=[])
    {
        return $this->loanRepo->getAllLoansPaginated($filterParameters,$select,$with);
    }
    public function getApiLoansPaginated($select= ['*'],$with=[])
    {
        return $this->loanRepo->getApiLoansPaginated($select,$with);
    }
    public function getSettledLoansPaginated($select= ['*'],$with=[])
    {
        return $this->loanRepo->getSettledLoansPaginated($select,$with);
    }

    /**
     * @throws Exception
     */
    public function findLoanById($id, $select=['*'], $with=[])
    {

            $LoanDetail =  $this->loanRepo->findLoanById($id,$select,$with);
            if(!$LoanDetail){
                throw new \Exception(__('message.loan_not_found'),400);
            }
            return $LoanDetail;

    }
    /**
     * @throws Exception
     */
    public function getLoanByEmployee($employeeId, $select=['*'], $with=[])
    {

        return  $this->loanRepo->findLoanByEmployeeId($employeeId,$select,$with);


    }
    /**
     * @throws Exception
     */
    public function getLoanById($loanId, $select=['*'], $with=[])
    {

        return  $this->loanRepo->find($loanId,$select,$with);


    }

    /**
     * @throws Exception
     */
    public function saveLoanDetail($validatedData)
    {
        $salary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($validatedData['employee_id'],['id','monthly_basic_salary']);


        if (!$salary) {
            throw new Exception('You cannot request Loan, your salary is not set.');
        }

        $loanAmountLimit = AppHelper::getMaxAllowedLoanAmountLimit();
        $limitAmount = $loanAmountLimit * $salary->monthly_basic_salary;
        if($limitAmount < $validatedData['loan_amount']){
            throw new Exception('Your requested loan amount exceeds allowed limit of '.$limitAmount);
        }

        $pendingLoan = $this->loanRepo->checkPendingLoan($validatedData['employee_id']);
        if($pendingLoan){
            throw new \Exception(__('message.loan_pending_error'),400);
        }

        $recentSettledLoan = $this->loanRepo->checkSettledLoansInCurrentMonthAndYear($validatedData['employee_id']);
        if($recentSettledLoan){
            throw new \Exception(__('message.recent_loan_settlement_error'),400);
        }

        $validatedData['application_date'] = date('Y-m-d');
        return $this->loanRepo->store($validatedData);

    }

    /**
     * @throws Exception
     */
    public function updateLoanDetail($id, $validatedData)
    {
        $loanDetail = $this->findLoanById($id);
        $salary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($loanDetail->employee_id,['id','monthly_basic_salary']);


        if (!$salary) {
            throw new Exception('You cannot request Loan, your salary is not set.');
        }

        $loanAmountLimit = AppHelper::getMaxAllowedLoanAmountLimit();
        $limitAmount = $loanAmountLimit * $salary->monthly_basic_salary;
        if($limitAmount < $validatedData['loan_amount']){
            throw new Exception('Your requested loan amount exceeds allowed limit of '.$limitAmount);
        }

        if(in_array($loanDetail->status, [LoanStatusEnum::approve->value, LoanStatusEnum::settled->value])){
            throw new Exception(__('index.loan_update_alert'),403);
        }
        return $this->loanRepo->update($loanDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteLoan($id)
    {

        $loanDetail = $this->findLoanById($id);

        return $this->loanRepo->delete($loanDetail);

    }

    /**
     * @throws Exception
     */
    public function changeLoanStatus($id, $validatedData)
    {
        $loanDetail = $this->findLoanById($id);

        $updateData = $validatedData;

        // If status is approved, calculate and store loan repayment schedule
        if (isset($validatedData['status']) && $validatedData['status'] === LoanStatusEnum::approve->value) {

            $repaymentData = $this->loanRepo->getEmployeeLoanRepayment($id,$loanDetail->employee_id);
            if(!$repaymentData){
                $this->generateLoanRepaymentSchedule($loanDetail, $validatedData['repayment_from']);
                $updateData['issue_date'] = date('Y-m-d');

            }

        }

        $this->loanRepo->update($loanDetail, $updateData);


        return $loanDetail->fresh(); // Or true, depending on your repo return
    }


    /**
     * @throws Exception
     */
    private function generateLoanRepaymentSchedule(Loan $loan, $repaymentFrom): void
    {
        $loanType = $loan->loanType;
        if (!$loanType) {
            throw new \Exception('Loan type not found for repayment calculation.');
        }

        if (!$repaymentFrom) {
            throw new \Exception('Repayment from date not set for the loan.');
        }

        $principal = $loan->loan_amount;
        $interestRate = $loanType->interest_rate; // Annual %
        $tenureMonths = $loanType->term;
        $interestType = $loanType->interest_type ?? LoanInterestTypeEnum::fixed->value;

        if ($tenureMonths <= 0) {
            throw new \Exception('Invalid tenure.');
        }

        $remainingPrincipal = $principal;
        $repaymentDate = Carbon::parse($repaymentFrom);
        $startDate = $repaymentDate->copy();
        $repayments = [];
        $monthlyRate = $interestRate / 12 / 100;

        if ($interestType === 'fixed') {
            // Flat rate: Total interest upfront, divide equally
            $tenureYears = $tenureMonths / 12;
            $totalInterest = $principal * $interestRate / 100 * $tenureYears;
            $monthlyInterest = $totalInterest / $tenureMonths;
            $monthlyPrincipal = $principal / $tenureMonths;
            $emi = $monthlyPrincipal + $monthlyInterest;

            for ($month = 1; $month <= $tenureMonths; $month++) {
                $paymentDate = $startDate->copy()->addMonths($month - 1);
                $interestAmount = $monthlyInterest;
                $principalAmount = $monthlyPrincipal;

                if ($month === $tenureMonths) {
                    $principalAmount = $remainingPrincipal;
                    $interestAmount = $emi - $principalAmount;
                }

                $repayments[] = [
                    'loan_id' => $loan->id,
                    'employee_id' => $loan->employee_id,
                    'principal_amount' => $principalAmount,
                    'interest_amount' => $interestAmount,
                    'payment_date' => $paymentDate,
                    'is_paid' => false,
                    'status' => LoanRepaymentStatusEnum::upcoming->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $remainingPrincipal -= $principalAmount;
                if ($remainingPrincipal <= 0) break;
            }
        } else { // 'declining' - Reducing balance with fixed EMI
            // EMI formula
            $emiNumerator = $principal * $monthlyRate * pow(1 + $monthlyRate, $tenureMonths);
            $emiDenominator = pow(1 + $monthlyRate, $tenureMonths) - 1;
            $emi = $emiNumerator / $emiDenominator;

            for ($month = 1; $month <= $tenureMonths; $month++) {
                $paymentDate = $startDate->copy()->addMonths($month - 1);

                $interestAmount = $remainingPrincipal * $monthlyRate;
                $principalAmount = $emi - $interestAmount;

                if ($month === $tenureMonths) {
                    $principalAmount = $remainingPrincipal;
                    $interestAmount = $emi - $principalAmount;
                }

                $repayments[] = [
                    'loan_id' => $loan->id,
                    'employee_id' => $loan->employee_id,
                    'principal_amount' => $principalAmount,
                    'interest_amount' => $interestAmount,
                    'payment_date' => $paymentDate,
                    'is_paid' => false,
                    'status' => LoanRepaymentStatusEnum::upcoming->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $remainingPrincipal -= $principalAmount;
                if ($remainingPrincipal <= 0) break;
            }
        }

        DB::table('loan_repayments')->insert($repayments);
    }







}

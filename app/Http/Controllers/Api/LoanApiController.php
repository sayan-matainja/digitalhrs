<?php

namespace App\Http\Controllers\Api;

use App\Enum\LoanRepaymentStatusEnum;
use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\EmployeeSalary;
use App\Requests\LoanManagement\EmployeeLoanApiRequest;
use App\Requests\LoanManagement\EmployeeLoanRequest;
use App\Requests\LoanManagement\EmployeeLoanSettlementApiRequest;
use App\Resources\Loan\LoanCollection;
use App\Resources\Loan\LoanDetailResource;
use App\Resources\Loan\LoanInstallmentResource;
use App\Resources\Loan\LoanRepaymentResource;
use App\Resources\Loan\LoanResource;
use App\Resources\Loan\LoanSettlementRequestCollection;
use App\Resources\Loan\LoanTypeCollection;
use App\Resources\Loan\LoanTypeResource;
use App\Services\LoanManagement\LoanRepaymentService;
use App\Services\LoanManagement\LoanService;
use App\Services\LoanManagement\LoanSettlementRequestService;
use App\Services\LoanManagement\LoanTypeService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class LoanApiController extends Controller
{
    use CustomAuthorizesRequests;

    public function __construct(protected LoanService $loanService, protected LoanRepaymentService $repaymentService,
                                protected LoanTypeService $typeService, protected LoanSettlementRequestService $settlementRequestService)
    {}

    public function getEmployeesLoanLists()
    {
        try {
            $select = ['*'];
            $with = [
                'loanType',
                'loanRepayment',
                'paymentMethod:id,name',
            ];

            $loanLists = $this->loanService->getApiLoansPaginated($select,$with);
            $data = new LoanCollection($loanLists);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getLoanTypes()
    {
        try {
            $user = auth()->user();
            $branchId = $user->branch_id;

            $select = ['*'];
            $loanTypes = $this->typeService->getBranchLoanTypes($branchId,$select);

            $data = new LoanTypeCollection($loanTypes);

            return AppHelper::sendSuccessResponse(__('index.data_found'), $data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }


    /**
     * @throws MessagingException
     * @throws FirebaseException
     * @throws AuthorizationException
     */
    public function storeLoanDetail(EmployeeLoanApiRequest $request)
    {
        $this->authorize('add_loan_request');
        try {
            $permissionKeyForNotification = 'employee_loan_request';

            $validatedData = $request->validated();
            $validatedData['loan_id'] = AppHelper::getLoanId();
            $validatedData['employee_id'] = auth()->user()->id;
            $validatedData['branch_id'] = auth()->user()->branch_id;
            $validatedData['department_id'] = auth()->user()->department_id;

            DB::beginTransaction();
                $this->loanService->saveLoanDetail($validatedData);
            DB::commit();
            $message = __('message.loan_request_message', ['name' => ucfirst(auth()->user()->name), 'amount' => bcdiv($validatedData['loan_amount'], 2),'reason' => $validatedData['loan_purpose']]);

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.loan_request_notification'),
                $message,
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_created_successfully'));
        }catch(Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     * @throws AuthorizationException
     */
    public function updateLoanDetail(EmployeeLoanApiRequest $request)
    {
        $this->authorize('update_loan_request');
        try {

            $permissionKeyForNotification = 'employee_loan_request';

            $loanId = $request->loan_id;

            $validatedData = $request->validated();
            DB::beginTransaction();
                $this->loanService->updateLoanDetail($loanId,$validatedData);
            DB::commit();

            $message = __('message.loan_request_message', ['name' => ucfirst(auth()->user()->name), 'amount' => bcdiv($validatedData['loan_amount'], 2),'reason' => $validatedData['loan_purpose']]);

            AppHelper::sendNotificationToAuthorizedUser(
                __('index.loan_request_notification'),
                $message,
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_updated_successfully'));
        }catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function repayment($loanId){
        try {
            $select = ['*'];
            $loanLists = $this->repaymentService->getAllRepayments($loanId, $select);
            $loan = $this->loanService->findLoanById($loanId,['*'],['loanType']);
            $totalSettlement = $loanLists->sum('settlement_amount');
            $totalInterest = $loanLists->sum('interest_amount');
            $totalPaidPrincipal = $loanLists->where('status',LoanRepaymentStatusEnum::paid->value)->sum('principal_amount');
            $totalPaidSettlement = $loanLists->where('status',LoanRepaymentStatusEnum::paid->value)->sum('settlement_amount');
            $totalPaidInterest = $loanLists->where('status',LoanRepaymentStatusEnum::paid->value)->sum('interest_amount');
            $totalPayment = $loan->loan_amount + $totalInterest;
            $totalPaid = $totalPaidPrincipal + $totalPaidSettlement;
            $totalRemaining = $totalPayment - $totalPaid - $totalPaidInterest;
            $firstRepayment = $loanLists->first();

            Log::info('$totalRemaining '. $totalRemaining);
            $monthlyEMI = $firstRepayment ? $firstRepayment->principal_amount + $firstRepayment->interest_amount : 0;

            $data['monthly_emi'] = bcdiv($monthlyEMI,1,2);
            $data['principal'] = bcdiv($loan->loan_amount,1,2);
            $data['total_settlement'] = bcdiv($totalSettlement,1,2);
            $data['total_interest'] = bcdiv($totalInterest,1,2);
            $data['total_interest_paid'] = bcdiv($totalPaidInterest,1,2);
            $data['total_payable'] = bcdiv($totalPayment,1,2);
            $data['total_paid_no_interest'] = bcdiv($totalPaid,1,2);
            $data['total_remaining'] = $totalRemaining > 0.01 ? bcdiv($totalRemaining ,1,2) : 0;
            $data['application_date'] = isset($loan->application_date) ? AppHelper::formatDateForView($loan->application_date) : '';
            $data['interest_rate'] = $loan->loanType->interest_rate ?? null;
            $data['currency'] = AppHelper::getCompanyPaymentCurrencySymbol();
            $repaymentList = [];
            $remainingBalance = $loan->loan_amount;
            $repayments = $loanLists->sortBy('payment_date');
            $totalCount = $repayments->count();
            $i = 0;
            foreach ($repayments as $repayment) {
                $i++;
                $schedule = $repayment->principal_amount + $repayment->interest_amount;
                $interest = $repayment->interest_amount;
                $status = $repayment->status;
                $principalPaid = $repayment->principal_amount;
                $interestPaid = $repayment->interest_amount;
                $settlement = $repayment->settlement_amount;
                $tentativeBalance = $remainingBalance - $settlement - $principalPaid;
                $isLast = ($i === $totalCount);
                $balance = $isLast ? 0.00 : max(0, $tentativeBalance);
                $totalSettlement = $settlement > 0 ? $settlement + $interestPaid + $principalPaid : $settlement;

                $repaymentList[] = [
                    'year' => AppHelper::getYearValue($repayment->payment_date),
                    'month' => AppHelper::getInstallmentDate($repayment->payment_date),
                    'schedule' => bcdiv($schedule,1,2),
                    'interest' => bcdiv($interest,1,2),
                    'principal' => bcdiv($principalPaid,1,2),
                    'settlement' => bcdiv($totalSettlement,1,2),
                    'balance' => bcdiv($balance,1,2),
                    'status' => $status,
                ];

                $remainingBalance = $balance;
            }

            $data['repayments'] = $repaymentList;
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }



    public function getLoanSettlementRequests(Request $request)
    {
        try {
            $select = ['*'];
            $with = [
                'loan:id,loan_id'
            ];
            $filterParameters['loan_id'] = $request->input('loan_id') ?? 'all';
            $filterParameters['employee_id'] = getAuthUserCode();
            $requestLists = $this->settlementRequestService->getSettlementRequests($filterParameters,$select,$with);
            $data = new LoanSettlementRequestCollection($requestLists);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }


    /**
     * @throws MessagingException
     * @throws FirebaseException
     * @throws AuthorizationException
     */
    public function storeSettlementRequest(EmployeeLoanSettlementApiRequest $request)
    {
        $this->authorize('request_settlement');
        try {
            $permissionKeyForNotification = 'employee_loan_request';

            $validatedData = $request->validated();
            $validatedData['employee_id'] = auth()->user()->id;
            $validatedData['branch_id'] = auth()->user()->branch_id;
            $validatedData['department_id'] = auth()->user()->department_id;

            DB::beginTransaction();
            $this->settlementRequestService->saveLoanSettlementRequest($validatedData);
            DB::commit();
            $message = __('message.loan_settlement_request_message', ['name' => ucfirst(auth()->user()->name), 'reason' => $validatedData['reason']]);
            AppHelper::sendNotificationToAuthorizedUser(
                __('message.loan_settlement_request_notification'),
                $message,
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_created_successfully'));
        }catch(Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     * @throws AuthorizationException
     */
    public function updateSettlementRequest(EmployeeLoanSettlementApiRequest $request)
    {
        $this->authorize('update_settlement');
        try {

            $permissionKeyForNotification = 'employee_loan_request';

            $requestId = $request->settlement_request_id;

            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->settlementRequestService->updateLoanSettlementRequest($requestId,$validatedData);
            DB::commit();

            $message = __('message.loan_settlement_request_message', ['name' => ucfirst(auth()->user()->name),'reason' => $validatedData['reason']]);

            AppHelper::sendNotificationToAuthorizedUser(
                __('message.loan_settlement_request_notification'),
                $message,
                $permissionKeyForNotification
            );
            return AppHelper::sendSuccessResponse(__('index.data_updated_successfully'));
        }catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function settlementDetail($loanId){
        try {
            $select = ['id','loan_id','loan_amount','loan_type_id'];
            $with = ['loanType',
                'loanRepayment' => function ($query) {
                    $query->select('id', 'loan_id', 'principal_amount','settlement_amount', 'status','employee_id')
                        ->where('status', LoanRepaymentStatusEnum::paid->value);
                }
            ];
            $loan = $this->loanService->getLoanById($loanId, $select, $with);

            if (!$loan) {
                return AppHelper::sendErrorResponse(__('message.loan_settlement_error'),400);
            }

            $paidPrincipal = $loan->loanRepayment->sum('principal_amount');
            $paidSettlement = $loan->loanRepayment->sum('settlement_amount');
            $totalPaid = $paidPrincipal + $paidSettlement;
            $remainingPrincipal = $loan->loan_amount - $totalPaid;

            $recentRepayment =  $this->repaymentService->getEmployeeRecentInstallment(getAuthUserCode());

            $pastRepayment = $this->repaymentService->getPastMonthLoanInstallment($recentRepayment);

            $interestStartDate = $pastRepayment
                ? $pastRepayment->payment_date
                : $recentRepayment->payment_date;


            $manualAmount = 0;
            $requestDate = Carbon::now()->startOfDay();
            $interestStartDate = Carbon::parse($interestStartDate)->startOfDay();
            $days = $interestStartDate->diffInDays($requestDate) +1;
            $salaryAmount = $recentRepayment->interest_amount + $remainingPrincipal;
            if ($loan->loanType->interest_type === 'fixed') {
                $manualAmount = ($loan->loan_amount * ($loan->loanType->interest_rate / 100 / 365) * $days) + $remainingPrincipal;
            } else if ($loan->loanType->interest_type === 'declining') {
                $manualAmount = ($remainingPrincipal * ($loan->loanType->interest_rate / 100 / 365) * $days) + $remainingPrincipal;
            }


            $data =[
                'interest_start_date' => AppHelper::formatDateForView($interestStartDate),
                'interest_rate'=>$loan->loanType->interest_rate,
                'interest_amount'=>$recentRepayment->interest_amount,
                'loan_amount'=> $loan->loan_amount,
                'interest_type'=>$loan->loanType->interest_type,
                'salaryAmount' => bcdiv($salaryAmount,1,2),
                'manualAmount' => bcdiv($manualAmount,1,2),
            ];

            Log::info('api loan settlement data '.json_encode($data));

            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

}

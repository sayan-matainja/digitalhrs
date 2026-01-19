<?php

namespace App\Http\Controllers\Web;

use App\Enum\LoanRepaymentStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\EmployeeSalary;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\GeneralSettingRepository;
use App\Repositories\UserRepository;
use App\Requests\GeneralSetting\GeneralSettingRequest;
use App\Requests\LoanManagement\EmployeeLoanRequest;
use App\Requests\LoanManagement\LoanRequest;
use App\Requests\LoanManagement\LoanUpdateStatusRequest;
use App\Services\LoanManagement\LoanRepaymentService;
use App\Services\LoanManagement\LoanService;
use App\Services\LoanManagement\LoanTypeService;
use App\Services\Notification\NotificationService;
use App\Services\Payroll\PaymentMethodService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class LoanController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.loanManagement.loan.';

    public function __construct(
        protected LoanService     $loanService,
        protected LoanTypeService $loanTypeService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository,
        protected DepartmentRepository $departmentRepository,
        protected PaymentMethodService $paymentMethodService,
        protected LoanRepaymentService $repaymentService,
        protected GeneralSettingRepository $generalSettingRepository,
        protected NotificationService $notificationService,
    ){}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('list_loan');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'type_id' => $request->type_id ?? null,
                'department_id' => $request->department_id ?? null,
//                'status' => $request->status ?? 'all',
            ];
            if (!auth('admin')->check() && auth()->check()) {
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = [
                'loanType:id,name',
                'department:id,dept_name',
                'employee:id,name,email',
                'loanRepayment'
            ];

            $settledLoanLists = $this->loanService->getSettledLoansPaginated($select, $with);

            $loanLists = $this->loanService->getAllLoansPaginated($filterParameters, $select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $paymentMethods = $this->paymentMethodService->pluckAllActivePaymentMethod(['id','name']);

            return view($this->view . 'index', compact('loanLists', 'companyDetail', 'filterParameters','paymentMethods','settledLoanLists'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('create_loan');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $loanId = AppHelper::getLoanId();
            return view($this->view . 'create',compact('companyDetail','loanId'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param LoanRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(LoanRequest $request)
    {
        $this->authorize('create_loan');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $loanDetail = $this->loanService->saveLoanDetail($validatedData);
            DB::commit();

            /**  notification to authorized users */
            $user = $this->userRepo->findUserDetailById($validatedData['employee_id'],['id','name']);
            $permissionKey = 'employee_loan_request';
            $message = __('message.loan_request_message', ['name' => ucfirst($user->name), 'reason' => $validatedData['loan_purpose']]);

            $this->sendNotification($loanDetail, $message, $permissionKey);


            /** to the employee */
            $description =  __('message.loan_notification_message_on_behalf', [
                'requester_name' => isset(auth()->user()->id) ? ucfirst(auth()?->user()?->name) : 'Admin',
                'reason' => $validatedData['loan_purpose'],
            ]);
            $this->sendNotification($loanDetail, $description);


            return redirect()->route('admin.loan.index')->with('success', __('message.loan_saved'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @throws AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('show_loan');

        try {

            $select = ['*'];
            $with = [
                'loanType',
                'department:id,dept_name',
                'employee:id,name,email',
                'branch:id,name',
                'loanRepayment',
                'paymentMethod:id,name',
            ];

            $loanDetail = $this->loanService->findLoanById($id, $select, $with);

            $nextRepayment = $loanDetail->loanRepayment
                ->where('status', 'upcoming')
                ->sortByDesc('payment_date')
                ->first();

            $lastRepayment = $loanDetail->loanRepayment->sortByDesc('payment_date')->first();

            // Prepare formatted details
            $data = [
                'loan_id' => $loanDetail->loan_id ?? 'N/A',
                'loan_title' => ucfirst($loanDetail->loanType->name ?? 'N/A'),
                'loan_amount' => AppHelper::formatCurrencyAmount($loanDetail->loan_amount ?? 0),
                'monthly_installment' => AppHelper::formatCurrencyAmount($loanDetail->monthly_installment ?? 0),
                'repayment_amount' => AppHelper::formatCurrencyAmount($loanDetail->repayment_amount ?? 0),
                'application_date' => isset($loanDetail->application_date) ?AppHelper::formatDateForView($loanDetail->application_date) : 'N/A',
                'issue_date' => isset($loanDetail->issue_date) ?AppHelper::formatDateForView($loanDetail->issue_date) : 'N/A',
                'repayment_from' => isset($loanDetail->repayment_from) ? AppHelper::formatDateForView($loanDetail->repayment_from):'N/A',
                'status' => ucfirst($loanDetail->status),
                'employee_name' => $loanDetail->employee->name ?? 'N/A',
                'department_name' => $loanDetail->department->dept_name ?? 'N/A',
                'branch_name' => $loanDetail->branch->name ?? 'N/A',
                'loan_purpose' => ucfirst($loanDetail->loan_purpose ?? 'N/A'),
                'interest_rate' => isset($loanDetail->loanType->interest_rate) ? number_format($loanDetail->loanType->interest_rate, 2).'%' : 'N/A',
                'interest_type' => ucfirst($loanDetail->loanType->interest_type ?? 'N/A'),
                'next_interest_amount' => isset($nextRepayment->interest_amount) ? AppHelper::formatCurrencyAmount($nextRepayment->interest_amount) : 'N/A',
                'loan_due_at' => $lastRepayment && $lastRepayment->payment_date ? AppHelper::formatDateForView($lastRepayment->payment_date) : 'N/A',
                'description' => removeHtmlTags($loanDetail->description ?? ''),
                'remarks' => removeHtmlTags($loanDetail->remarks ?? ''),
                'attachment' => $loanDetail->attachment ? asset(Loan::UPLOAD_PATH . $loanDetail->attachment) : '',
                'updated_by' => isset($loanDetail->updated_by) ? $loanDetail->updatedBy->name : ($loanDetail->status == LoanStatusEnum::approve->value ? 'Admin' : 'N/A'),
                'payment_method' => isset($loanDetail->paymentMethod->name) ? ucfirst($loanDetail->paymentMethod->name) : 'N/A',
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('edit_loan');
        try {
            $loanDetail = $this->loanService->findLoanById($id);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'edit', compact('loanDetail','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function update(LoanRequest $request, $id)
    {
        $this->authorize('edit_loan');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
                $this->loanService->updateLoanDetail($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.loan.index')
                ->with('success', __('message.loan_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('delete_loan');
        try {
            DB::beginTransaction();
                $this->loanService->deleteLoan($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.loan_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchLoanData($branchId)
    {
        try {
            $types = $this->loanTypeService->getBranchLoanTypes($branchId, ['id','name','interest_rate','interest_type','term']);
            $departments = $this->departmentRepository->getAllActiveDepartmentsByBranchId($branchId,[],['id','dept_name']);
            return response()->json([
                'types' => $types,
                'departments' => $departments,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    /**
     * @param $branchId
     * @return JsonResponse
     */
    public function getBranchLoanType($branchId)
    {
        try {

            $types = $this->loanTypeService->getBranchLoanTypes($branchId, ['id','name','interest_rate','interest_type','term']);
            return response()->json([
                'types' => $types,
            ]);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }

    public function getEmployeeLoan($employeeId)
    {
        try {

            $select = ['id','loan_id','loan_amount','loan_type_id'];
            $with = ['loanType',
                'loanRepayment' => function ($query) {
                    $query->select('id', 'loan_id', 'principal_amount','settlement_amount', 'status','employee_id')
                        ->where('status', LoanRepaymentStatusEnum::paid->value);
                }
            ];
            $loan = $this->loanService->getLoanByEmployee($employeeId, $select, $with);

            if (!$loan) {
                return response()->json([
                    'loan' => null,
                    'remaining_principal' => 0,
                    'monthly_salary' => null,
                ]);
            }

            $paidPrincipal = $loan->loanRepayment->sum('principal_amount');
            $paidSettlement = $loan->loanRepayment->sum('settlement_amount');
            $totalPaid = $paidPrincipal + $paidSettlement;
            $remainingPrincipal = $loan->loan_amount - $totalPaid;

            $recentRepayment =  $this->repaymentService->getEmployeeRecentInstallment($employeeId);

            $pastRepayment = $this->repaymentService->getPastMonthLoanInstallment($recentRepayment);

            $interestStartDate = $pastRepayment
                ? $pastRepayment->payment_date
                : $recentRepayment->payment_date;

            $salary = EmployeeSalary::where('employee_id', $employeeId)
                ->select('id','monthly_basic_salary')
                ->first();

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

            $data = [
                'loan' => [
                    'id' => $loan->id,
                    'loan_id' => $loan->loan_id,
                ],
                'interest_start_date' => $interestStartDate,
                'interest_rate'=>$loan->loanType->interest_rate,
                'interest_amount'=>$recentRepayment->interest_amount,
                'loan_amount'=>$loan->loan_amount,
                'interest_type'=>$loan->loanType->interest_type,
                'monthly_salary' => $salary ? $salary->monthly_basic_salary : null,
                'manualAmount' =>$manualAmount,
                'salaryAmount' =>$salaryAmount,
            ];
            Log::info('api loan settlement data '.json_encode($data));

            return response()->json($data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());
        }
    }


    public function changeLoanStatus(LoanUpdateStatusRequest $request, $id)
    {
        $this->authorize('edit_loan');
        try {

            $validatedData = $request->validated();
            $loanDetail =$this->loanService->changeLoanStatus($id, $validatedData);

            /** to the employee */


            $description = __('message.loan_notification_status_update',['date'=>date('M d Y', strtotime($loanDetail->application_date)),'reason'=>$loanDetail->loan_reason,'status'=>$validatedData['status']]);

            $this->sendNotification($loanDetail, $description);

            return redirect()->back()->with('success', __('message.loan_status_change'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function requestLoan()
    {
        $this->authorize('request_loan');
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $loanId = AppHelper::getLoanId();
            return view($this->view . 'request',compact('companyDetail','loanId'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param EmployeeLoanRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function storeLoan(EmployeeLoanRequest $request)
    {
        $this->authorize('request_loan');
        try {
            $validatedData = $request->validated();
            $validatedData['employee_id'] = auth()->user()->id;
            $validatedData['branch_id'] = auth()->user()->branch_id;
            $validatedData['department_id'] = auth()->user()->department_id;
            DB::beginTransaction();
             $loanDetail =$this->loanService->saveLoanDetail($validatedData);
            DB::commit();
            $permissionKey = 'employee_loan_request';
            $message = __('message.loan_request_message', ['name' => ucfirst(auth()->user()->name), 'amount' => number_format($validatedData['loan_amount'], 2),'reason' => $validatedData['loan_purpose']]);


            $this->sendNotification($loanDetail, $message,$permissionKey );

            return redirect()->route('admin.loan-request.index')->with('success', __('message.loan_request'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @throws AuthorizationException
     */
    public function showHistory($id)
    {
        $this->authorize('loan_history');

        try {

            $select = ['*'];
            $with = [
                'loanType',
                'department:id,dept_name',
                'employee:id,name,email',
                'branch:id,name',
                'loanRepayment',
                'paymentMethod:id,name',
                'settlementRequest.approvedBy:id,name',
                'updatedBy:id,name'
            ];

            $loanDetail = $this->loanService->findLoanById($id, $select, $with);


            return view($this->view . 'history',compact('loanDetail'));

        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @throws AuthorizationException
     */
    public function setting()
    {
        $this->authorize('loan_setting');

        try {

            $settings = $this->generalSettingRepository->getLoanSetting();
            return view($this->view . 'setting',compact('settings'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function updateSetting(GeneralSettingRequest $request, $id)
    {
        $this->authorize('loan_setting');

        try {

            $validatedData = $request->validated();
            $generalSettingDetail = $this->generalSettingRepository->findOrFailGeneralSettingDetailById($id);

            $this->generalSettingRepository->update($generalSettingDetail, $validatedData);
            return redirect()->back()->with('success', 'Loan Setting Updated Successfully');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws Exception
     */
    private function sendNotification($loanDetail, $message, $permissionKey = '')
    {
        $title = __('message.loan_notification');
        $notificationData['title'] = $title;
        $notificationData['type'] = 'loan';
        $notificationData['description'] = $message;
        $notificationData['notification_for_id'] = $loanDetail['id'];

        if(!empty($permissionKey)){
            $userIds =  AppHelper::getAllUserIdsWithGivenPermission($permissionKey);
            $notificationData['user_id'] =$userIds;
            $notification = $this->notificationService->store($notificationData);

            if($notification){
                if(!empty($roleIds)){
                    SMPushHelper::sendLoanNotifications($title, $message,$userIds);
                }
            }
        }else{
            $notificationData['user_id'] = $loanDetail['employee_id'];
            $notification = $this->notificationService->store($notificationData);

            if($notification){
                SMPushHelper::sendLoanNotification($title, $message, $loanDetail['employee_id']);
            }
        }



    }

}

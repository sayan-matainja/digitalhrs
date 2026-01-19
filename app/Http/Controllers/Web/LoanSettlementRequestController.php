<?php

namespace App\Http\Controllers\Web;

use App\Enum\LoanStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\LoanManagement\EmployeeLoanSettlementRequest;
use App\Requests\LoanManagement\LoanRequest;
use App\Requests\LoanManagement\LoanSettlementRequest;
use App\Requests\LoanManagement\LoanSettlementUpdateStatusRequest;
use App\Requests\LoanManagement\LoanUpdateStatusRequest;
use App\Services\LoanManagement\LoanService;
use App\Services\LoanManagement\LoanSettlementRequestService;
use App\Services\LoanManagement\LoanTypeService;
use App\Services\Payroll\PaymentMethodService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class LoanSettlementRequestController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.loanManagement.settlementRequest.';

    public function __construct(
        protected LoanService     $loanService,
        protected LoanSettlementRequestService     $settlementRequestService,
        protected LoanTypeService $loanTypeService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository,
        protected DepartmentRepository $departmentRepository,
        protected PaymentMethodService $paymentMethodService,
    ){}


    public function index(Request $request)
    {
        try {

            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'department_id' => $request->department_id ?? null,
                'status' => $request->status ?? 'all',
            ];
            if (!auth('admin')->check() && auth()->check()) {
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];
            $with = [
                'department:id,dept_name',
                'employee:id,name,email',
                'loan:id,loan_id,loan_amount',
            ];
            $requestLists = $this->settlementRequestService->getSettlementRequests($filterParameters, $select, $with);

            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $paymentMethods = $this->paymentMethodService->pluckAllActivePaymentMethod(['id','name']);

            return view($this->view . 'index', compact('requestLists', 'companyDetail', 'filterParameters','paymentMethods'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function create()
    {
        try {
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'create',compact('companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param LoanSettlementRequest $request
     * @return RedirectResponse
     * @throws FirebaseException
     * @throws MessagingException
     */
    public function store(LoanSettlementRequest $request)
    {
        try {
            $validatedData = $request->validated();

            DB::beginTransaction();
            $this->settlementRequestService->saveLoanSettlementRequest($validatedData);
            DB::commit();

            $title = __('message.loan_settlement_request_notification');
            /**  notification to authorized users */
            $user = $this->userRepo->findUserDetailById($validatedData['employee_id'],['id','name']);
            $permissionKey = 'employee_loan_request';
            $message = __('message.loan_settlement_request_message', ['name' => ucfirst($user->name), 'reason' => $validatedData['reason']]);

            AppHelper::sendNotificationToAuthorizedUser(
                $title,
                $message,
                $permissionKey
            );

            /** to the employee */
            $description =  __('message.loan_settlement_notification_message_on_behalf', [
                'requester_name' => isset(auth()->user()->id) ? ucfirst(auth()?->user()?->name) : 'Admin',
                'reason' => $validatedData['reason'],
            ]);


            SMPushHelper::sendLoanNotification($title, $description,$validatedData['employee_id']);

            return redirect()->route('admin.request-settlement.index')->with('success', __('message.loan_settlement_saved'));
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
                'department:id,dept_name',
                'employee:id,name,email',
                'branch:id,name',
                'loan:id,loan_id'
            ];

            $requestDetail = $this->settlementRequestService->findSettlementRequestById($id, $select, $with);

            // Prepare formatted details
            $data = [
                'loan_id' => $requestDetail->loan->loan_id ?? 'N/A',
                'branch_name' => $requestDetail->branch->name ?? 'N/A',
                'department_name' => $requestDetail->department->dept_name ?? 'N/A',
                'employee_name' => $requestDetail->employee->name ?? 'N/A',
                'requested_date' => isset($requestDetail->created_at) ?AppHelper::formatDateForView($requestDetail->created_at) : 'N/A',
                'settlement_type' => ucfirst($requestDetail->settlement_type),
                'settlement_method' => ucfirst($requestDetail->settlement_method),
                'amount' => AppHelper::formatCurrencyAmount($requestDetail->amount),
                'status' => ucfirst($requestDetail->status),
                'reason' => removeHtmlTags($requestDetail->reason ?? ''),
                'remarks' => removeHtmlTags($requestDetail->remarks ?? ''),
                'approved_by' => isset($requestDetail->approved_by ) ? $requestDetail->approvedBy->name : ($requestDetail->status == LoanStatusEnum::approve->value ? 'Admin' : 'N/A'),

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


    public function edit($id)
    {
        try {
            $requestDetail = $this->settlementRequestService->findSettlementRequestById($id);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view . 'edit', compact('requestDetail','companyDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function update(LoanSettlementRequest $request, $id)
    {
        try {

            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->settlementRequestService->updateLoanSettlementRequest($id, $validatedData);
            DB::commit();
            return redirect()->route('admin.request-settlement.index')
                ->with('success', __('message.loan_settlement_update'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }


    public function delete($id)
    {
        try {
            DB::beginTransaction();
                $this->settlementRequestService->deleteLoanSettlementRequest($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.loan_settlement_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @param LoanSettlementUpdateStatusRequest $request
     * @param $id
     * @return RedirectResponse
     */
    public function changeSettlementRequestStatus(LoanSettlementUpdateStatusRequest $request, $id): RedirectResponse
    {
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $requestDetail =$this->settlementRequestService->changeLoanSettlementRequestStatus($id, $validatedData);
            DB::commit();
            /** to the employee */
            $title = __('message.loan_settlement_request_notification');

            $description = __('message.loan_settlement_notification_status_update',['date'=>date('M d Y', strtotime($requestDetail->created_at)),'status'=>$validatedData['status']]);

            SMPushHelper::sendLoanNotification($title, $description,$requestDetail->employee_id);

            return redirect()->back()->with('success', __('message.loan_settlement_status_change'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    public function requestLoanSettlement()
    {
        try {
            $loans =$this->loanService->getLoanByEmployee(auth()->user()->id,['id','loan_id']);
            return view($this->view . 'request',compact('loans'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }


    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    public function storeLoanSettlement(EmployeeLoanSettlementRequest $request)
    {

        try {
            $validatedData = $request->validated();
            $validatedData['employee_id'] = auth()->user()->id;
            $validatedData['branch_id'] = auth()->user()->branch_id;
            $validatedData['department_id'] = auth()->user()->department_id;
            DB::beginTransaction();
            $this->settlementRequestService->saveLoanSettlementRequest($validatedData);
            DB::commit();
            $permissionKey = 'employee_loan_request';
            $message = __('message.loan_settlement_request_message', ['name' => ucfirst(auth()->user()->name),'reason' => $validatedData['reason']]);

            AppHelper::sendNotificationToAuthorizedUser(
                __('message.loan_settlement_request_notification'),
                $message,
            $permissionKey
            );
            return redirect()->back()->with('success', __('message.loan_settlement_request'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}

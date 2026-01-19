<?php

namespace App\Http\Controllers\Web;

use App\Enum\LoanStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\LoanManagement\EmployeeLoanRequest;
use App\Requests\LoanManagement\LoanRequest;
use App\Requests\LoanManagement\LoanUpdateStatusRequest;
use App\Services\LoanManagement\LoanRepaymentService;
use App\Services\LoanManagement\LoanService;
use App\Services\LoanManagement\LoanTypeService;
use App\Services\Payroll\PaymentMethodService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanRepaymentController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.loanManagement.loan.';

    public function __construct(
        protected LoanService     $loanService,
        protected LoanRepaymentService     $repaymentService,
        protected LoanTypeService $loanTypeService,
        protected UserRepository   $userRepo,
        protected CompanyRepository $companyRepository,
        protected DepartmentRepository $departmentRepository,
        protected PaymentMethodService $paymentMethodService,
    ){}

    /**
     * @throws AuthorizationException
     */
    public function repaymentList(Request $request)
    {
        $this->authorize('repayment_list');
        try {
            $filterParameters = [
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'type_id' => $request->type_id ?? null,
                'status' => $request->status ?? 'all',
            ];
            if (!auth('admin')->check() && auth()->check()) {
                $filterParameters['branch_id'] = auth()->user()->branch_id;
            }

            $select = ['*'];

            $with = [
                'loanType',
                'department:id,dept_name',
                'employee:id,name,email',
                'branch:id,name',
                'loanRepayment'
            ];
            ;
            $loanLists = $this->repaymentService->getAllRepaymentsPaginated($filterParameters, $select, $with);


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);


            return view($this->view . 'repayment_list', compact('loanLists', 'companyDetail', 'filterParameters'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function repaymentDetail($loanId){
        try {
            $select = ['*'];
            $with = [
                'loan.loanType:id,name',
                'employee:id,name,email',
            ];
            $loanLists = $this->repaymentService->getAllRepayments($loanId, $select, $with);

            $loan = $this->loanService->findLoanById($loanId,['*'],['settlementRequest:id,remarks,created_at']);


            return view($this->view . 'repayment', compact('loanLists','loan'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }



}

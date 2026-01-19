<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\SalaryReviseHistory;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Requests\Payroll\ReviseSalary\ReviseSalaryRequest;
use App\Services\FiscalYear\FiscalYearService;
use App\Services\Payroll\SalaryReviseHistoryService;
use App\Traits\CustomAuthorizesRequests;
use Exception;

class SalaryHistoryController extends Controller
{
    use CustomAuthorizesRequests;
    public $view = 'admin.payroll.employeeSalary.';

    public function __construct(
        protected SalaryReviseHistoryService $salaryHistoryService,
        protected UserRepository $userRepo,
        protected EmployeeSalaryRepository $employeeSalaryRepository,
        protected FiscalYearService $fiscalYearService,
    ){}

    public function getEmployeeAllSalaryHistory($employeeId)
    {
        try{
            $this->authorize('show_salary_history');
            $select = ['*'];

            $salaryReviseLists = $this->salaryHistoryService->getEmployeeAllSalaryHistory($employeeId,$select);
            $employeeDetail = $this->userRepo->findUserDetailById($employeeId,['id','name']);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            return view($this->view.'salary_history',compact('salaryReviseLists','employeeDetail','currency'));
        }catch (Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function create($employeeId)
    {
        try{
            $this->authorize('salary_increment');
            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId,['annual_salary']);

            $employeeDetail = $this->userRepo->findUserDetailById($employeeId,['id','name']);
            $dateInBs = AppHelper::ifDateInBsEnabled();

             $fiscalYear = $this->fiscalYearService->getActiveFiscalYear();
            return view($this->view.'increment_salary',compact('employeeDetail','employeeSalary','fiscalYear','dateInBs'));
        }catch(Exception $exception){
            return redirect()->back()->with('danger',$exception->getMessage());
        }
    }

    public function store(ReviseSalaryRequest $request)
    {
        try{
            $this->authorize('salary_increment');
            $validatedData = $request->validated();

            $this->salaryHistoryService->store($validatedData);
            return redirect()
                ->route('admin.employee-salaries.index')
                ->with('success',__('message.salary_history_update'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger',$exception->getMessage())
                ->withInput();
        }
    }
}

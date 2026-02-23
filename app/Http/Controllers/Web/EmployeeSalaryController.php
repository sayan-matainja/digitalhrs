<?php

namespace App\Http\Controllers\Web;

use App\Enum\EmployeeBasicSalaryTypeEnum;
use App\Enum\PayslipStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\DateConverter;
use App\Helpers\NepaliDate;
use App\Helpers\PayrollHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\EmployeeAccount;
use App\Models\EmployeePayslipDetail;
use App\Models\UnderTimeSetting;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\EmployeePayslipAdditionalRepository;
use App\Repositories\EmployeePayslipDetailRepository;
use App\Repositories\EmployeePayslipRepository;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\SalaryGroupEmployeeRepository;
use App\Repositories\UserAccountRepository;
use App\Repositories\UserRepository;
use App\Services\LoanManagement\LoanRepaymentService;
use App\Services\LoanManagement\LoanService;
use App\Services\Notification\NotificationService;
use App\Services\Payroll\AdvanceSalaryService;
use App\Services\Payroll\BonusService;
use App\Services\Payroll\GeneratePayrollService;
use App\Services\Payroll\PaymentMethodService;
use App\Services\Payroll\SalaryComponentService;
use App\Services\Payroll\SalaryGroupService;
use App\Services\Payroll\SalaryTDSService;
use App\Services\Payroll\UnderTimeSettingService;
use App\Services\Tada\TadaService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use MilanTarami\NumberToWordsConverter\Services\NumberToWords;

class EmployeeSalaryController extends Controller
{
    use CustomAuthorizesRequests;
    public $view = 'admin.payroll.employeeSalary.';

    public function __construct(
        public UserRepository $userRepository,
        public UserAccountRepository $userAccountRepo,
        public GeneratePayrollService $generatePayrollService,
        public EmployeeSalaryRepository $employeeSalaryRepository,
        public SalaryGroupEmployeeRepository $salaryGroupEmployeeRepository,
        public SalaryGroupService $salaryGroupService,
        public EmployeePayslipRepository $payslipRepository,
        public EmployeePayslipDetailRepository $payslipDetailRepository,
        public DepartmentRepository $departmentRepository,
        public TadaService $tadaService,
        public PaymentMethodService $paymentMethodService,
        public AdvanceSalaryService $advanceSalaryService,
        public BranchRepository $branchRepo,
        public UnderTimeSettingService $utSettingService,
        public SalaryComponentService $salaryComponentService,
        public SalaryTDSService $salaryTDSService,
        public BonusService $bonusService,
        public NotificationService $notificationService,
        public CompanyRepository $companyRepository,
        protected EmployeePayslipAdditionalRepository $additionalRepository,
        protected LoanRepaymentService $repaymentService,
    ){}

    public function index(Request $request): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('view_salary_list');

            $filterParameters = [
                'employee_name' => $request->employee_name ?? null,
                'department_id' => $request->department_id ?? null,
                'branch_id' => $request->branch_id ?? null
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch'] = auth()->user()->branch_id;
            }
            $employeeLists = $this->userRepository->getAllVerifiedActiveEmployeeWithSalaryGroup($filterParameters);


            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            return view($this->view.'index',compact('employeeLists','filterParameters','companyDetail'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function changeSalaryCycle($employeeId,$salaryCycle)
    {
        try{
            $this->authorize('change_salary_cycle');

            $employeeAccountDetail = $this->userAccountRepo->findAccountDetailByEmployeeId($employeeId);
            if(!$employeeAccountDetail){
                throw new Exception(__('message.account_not_found'),404);
            }
            if(!in_array($salaryCycle,EmployeeAccount::SALARY_CYCLE)){
                throw new Exception(__('message.invalid_cycle'),400);
            }
            $updateCycle = $this->userAccountRepo->updateEmployeeSalaryCycle($employeeAccountDetail,$salaryCycle);
            return redirect()->back()->with('success',__('message.salary_cycle_update',['cycle'=>ucfirst($updateCycle->salary_cycle)]));

        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

//    public function updateGeneratePayrollStatus($employeeId)
//    {
//        try{
//            $employeeAccountDetail = $this->userAccountRepo->findAccountDetailByEmployeeId($employeeId);
//            if(!$employeeAccountDetail){
//                throw new Exception('Employee Account Detail Not Found',404);
//            }
//            $this->userAccountRepo->toggleAllowGeneratePayrollStatus($employeeAccountDetail);
//            return redirect()->back()->with('success', 'Generate Payroll Status Updated Successfully');
//        }catch(Exception $exception){
//            return redirect()
//                ->back()
//                ->with('danger', $exception->getMessage());
//        }
//    }

    public function payrollCreate(Request $request)
    {
        try{

            $payrollData = $this->generatePayrollService->getEmployeeSalariesToCreatePayslip();

            return view($this->view.'generate_payslip',compact('payrollData'));
        }catch(Exception $exception){
            return response()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function payroll(Request $request): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('view_payroll_list');


            $filterData = [];

            if($request->all()){
                $validator = Validator::make($request->all(), [
                    'salary_cycle' => ['required','in:monthly,weekly'],
                    'year' => ['required'],
                    'month' => ['nullable','required_if:salary_cycle,monthly'],
                    'week' => ['nullable','required_if:salary_cycle,weekly'],
                    'include_tds' => ['nullable'],
                    'include_ssf' => ['nullable'],
                    'include_pf' => ['nullable'],
                    'include_tada' => ['nullable'],
                    'include_advance_salary'=> ['nullable'],
                    'attendance'=>['nullable'], //✅ ADD THIS LINE
                    'absent_paid'=>['nullable'],
                    'department_id'=>['nullable'],
                    'branch_id'=>['required'],
                    'approved_paid_leaves'=>['nullable'],

                ]);
                if ($validator->fails()) {
                    return redirect()->back() ->withErrors($validator);
                }

                $filterData = $validator->validated();

                $filterData['include_tada'] =  $filterData['include_tada'] ?? 0;
                $filterData['include_advance_salary'] =  $filterData['include_advance_salary'] ?? 0;
                $filterData['include_tds'] =  $filterData['include_tds'] ?? 0;
                $filterData['include_ssf'] =  $filterData['include_ssf'] ?? 0;
                $filterData['include_pf'] =  $filterData['include_pf'] ?? 0;
                $filterData['attendance'] =  $filterData['attendance'] ?? 0;  // ✅ ADD THIS LINE


                $payrolls = $this->generatePayrollService->getEmployeeSalariesToCreatePayslip($filterData);
            }else {
                $payrolls = $this->generatePayrollService->getCurrentEmployeeSalaries();
            }

            $employees = $this->userRepository->getAllEmployeesForPayroll();
            $paymentMethods = $this->paymentMethodService->pluckAllActivePaymentMethod(['id','name']);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();

            $companyId = AppHelper::getAuthUserCompanyId();

            $payslipStatus = EmployeePayslipDetail::PAYSLIP_STATUS;
            $salaryCycles = EmployeeAccount::SALARY_CYCLE;
            $branches = $this->branchRepo->getLoggedInUserCompanyBranches($companyId,['id','name']);

            $isBSDate = AppHelper::ifDateInBsEnabled();
            $months = AppHelper::getMonthsList();
            $currentNepaliYearMonth = AppHelper::getCurrentYearMonth();

            return view($this->view.'payroll', compact('payslipStatus', 'salaryCycles','payrolls', 'filterData','paymentMethods','currency','employees','branches','months','isBSDate','currentNepaliYearMonth'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function viewPayroll($employeePayslipId): Factory|View|RedirectResponse|Application
    {
        $this->authorize('show_payroll_detail');

        try{
            $imagePath = User::AVATAR_UPLOAD_PATH;
            $payrollData = $this->generatePayrollService->getEmployeeAccountDetailToCreatePayslip($employeePayslipId);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $underTimeSetting = $this->utSettingService->getAllUTList(['is_active'],1);

            return view($this->view.'payslip', compact('payrollData','imagePath','currency','underTimeSetting'));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function editPayroll($employeePayslipId): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('edit_payroll');
            $payrollData = $this->generatePayrollService->getEmployeeAccountDetailToCreatePayslip($employeePayslipId);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $underTimeSetting = $this->utSettingService->getAllUTList(['is_active'],1);

            $paymentMethods = $this->paymentMethodService->pluckAllActivePaymentMethod(['id','name']);
            $paidStatus = PayslipStatusEnum::paid->value;

            return view($this->view.'edit_payslip', compact('payrollData','currency','underTimeSetting','paidStatus','paymentMethods'));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function updatePayroll(Request $request, $employeePayslipId): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('edit_payroll');
            $validatedData = $request->all();


            $employeePayslipData = [
                "paid_on" => ($validatedData['status'] == PayslipStatusEnum::paid->value) ? $validatedData['paid_on']: null,
                "status" => $validatedData['status'],
                "monthly_basic_salary" => $validatedData['monthly_basic_salary'],
                "monthly_fixed_allowance" => $validatedData['monthly_fixed_allowance'],
                "tds" => $validatedData['tds'] ?? 0,
                "ssf_deduction" => $validatedData['ssf_deduction'] ?? 0,
                "bonus" => $validatedData['bonus'] ?? 0,
                "advance_salary" => $validatedData['advance_salary'] ?? 0,
                "tada" => $validatedData['tada'] ?? 0,
                "net_salary" => $validatedData['net_salary'],
                "absent_deduction" => $validatedData['absent_deduction'] ?? 0,
                "overtime" => $validatedData['overtime'] ?? 0,
                "undertime" => $validatedData['undertime'] ?? 0,
                "loan_amount" => $validatedData['loan_amount'] ?? 0,
                "payment_method_id" => $validatedData['payment_method_id'] ?? null,
            ];


            $employeePaySlipDetail = $this->payslipRepository->find($employeePayslipId);

            if($validatedData['status'] == PayslipStatusEnum::paid->value){
                $payslipYearMonth = AppHelper::getYearMonth($employeePaySlipDetail->salary_from);
                $payslipYear = $payslipYearMonth['year'];
                $payslipMonth = $payslipYearMonth['month'];
                $isBsEnabled = AppHelper::ifDateInBsEnabled();
                if($isBsEnabled){
                    $currentYearMonth = AppHelper::getCurrentYearMonth();
                    $currentYear = $currentYearMonth['year'];
                    $currentMonth = $currentYearMonth['month'];
                }else{
                    $currentYear = date('Y');
                    $currentMonth = date('m');
                }
                if(($payslipYear > $currentYear || ($currentYear == $payslipYear && $payslipMonth >= $currentMonth))){
                    throw new Exception(__('message.payslip_payment_error'),400);
                }

            }


            DB::beginTransaction();

                if($validatedData['status'] == PayslipStatusEnum::paid->value)
                {
                    if($employeePaySlipDetail->tada > 0){
                        $updateData = [
                            'is_settled'=>1,
                            'remark'=>'included in salary.',
                            'verified_by'=>auth()->user()?->id,
                        ];
                        $this->tadaService->makeSettlement($updateData, $employeePaySlipDetail);
                    }

                    // make advance salary settlement
                    if($employeePaySlipDetail->advance_salary > 0){
                        $advanceData = [
                            'is_settled'=>true,
                            'remark' => 'settled in payroll'
                        ];
                        $this->advanceSalaryService->advanceSalarySettlement($employeePaySlipDetail, $advanceData);
                    }

                    // make loan payment
                    if($employeePaySlipDetail->loan_amount > 0){
                        $this->repaymentService->loanSettlement($employeePaySlipDetail,$validatedData['paid_on']);
                    }

                }
                $this->payslipRepository->update($employeePaySlipDetail, $employeePayslipData);


                /** payslip detail update for components */
                if(isset($validatedData['component_amount'])){
                    foreach ($validatedData['component_amount'] as $key => $value) {
                        $payslipDetail = $this->payslipDetailRepository->find($employeePayslipId, $key);

                        $this->payslipDetailRepository->update($payslipDetail, ['amount' => $value]);

                    }
                }
                /** payslip additional component update */
                if(isset($validatedData['additional_component_amount'])){
                    foreach ($validatedData['additional_component_amount'] as $key => $value) {
                        $payslipDetail = $this->additionalRepository->find($employeePayslipId, $key);

                        $this->payslipDetailRepository->update($payslipDetail, ['amount' => $value]);

                    }
                }

            DB::commit();
            return redirect()->route('admin.employee-salary.payroll-detail',$employeePayslipId)->with('success', __('message.payroll_update'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function deletePayroll(Request $request, $employeePayslipId): RedirectResponse
    {
        try{
            $this->authorize('delete_payroll');
            $employeePaySlipDetail = $this->payslipRepository->find($employeePayslipId);

            if($employeePaySlipDetail->status == PayslipStatusEnum::paid->value  || $employeePaySlipDetail->status == PayslipStatusEnum::locked->value){
                return redirect()->back()->with('danger',__('message.payroll_delete_error'));
            }

            DB::beginTransaction();
                $this->payslipDetailRepository->deleteByPayslipId($employeePayslipId);
                $this->additionalRepository->deleteByPayslipId($employeePayslipId);
                $this->payslipRepository->delete($employeePaySlipDetail);
            DB::commit();

            return redirect()->route('admin.employee-salary.payroll')->with('success', __('message.payroll_delete'));
        }catch(Exception $exception){
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    private function filterComponentsByType($components, $type): array
    {
        return array_filter($components, function ($value) use ($type) {
            return $value['component_type'] === $type;
        });
    }

    /**
     * @return Factory|View|RedirectResponse|Application
     */
    public function createSalary($employeeId): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('add_salary');
            $salaryComponents = [];
            $employee = $this->userRepository->findUserDetailById($employeeId,['id','name']);
            $percentType = EmployeeBasicSalaryTypeEnum::percent->value;
            $fixedType = EmployeeBasicSalaryTypeEnum::fixed->value;

            $employeeSalaryGroup = $this->salaryGroupEmployeeRepository->getSalaryGroupFromEmployeeId($employeeId);

            if($employeeSalaryGroup)
            {
                $salaryGroup = $this->salaryGroupService->findOrFailSalaryGroupDetailById($employeeSalaryGroup->salary_group_id, ['*'], ['salaryComponents']);

                if($salaryGroup)
                {
                    $salaryComponents = $salaryGroup->salaryComponents->toArray();
                }
            }
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();


            return view($this->view.'add_salary', compact('employee','percentType','fixedType','employeeSalaryGroup','salaryComponents','currency'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $employeeId
     * @return RedirectResponse
     */
    public function saveSalary(Request $request, $employeeId): RedirectResponse
    {
        try{
            $this->authorize('add_salary');
            $validatedData = $request->all();

            $employeeSalaryGroup = $this->salaryGroupEmployeeRepository->getSalaryGroupFromEmployeeId($employeeId);

            if($employeeSalaryGroup)
            {
                $validatedData['salary_group_id'] = $employeeSalaryGroup->salary_group_id;
            }

            $validatedData['weekly_basic_salary'] = round(($validatedData['annual_basic_salary']/52),2);
            $validatedData['weekly_fixed_allowance'] = round(($validatedData['annual_fixed_allowance']/52),2);

            DB::beginTransaction();
                $this->employeeSalaryRepository->store($validatedData);
            DB::commit();

            return redirect()->route('admin.employee-salaries.index')->with('success',__('message.salary_add'));


        }catch(Exception $exception){
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }


    /**
     * @return Factory|View|RedirectResponse|Application
     */
    public function editSalary($employeeId): Factory|View|RedirectResponse|Application
    {
        try{
            $this->authorize('edit_salary');
            $salaryComponents = [];
            $employee = $this->userRepository->findUserDetailById($employeeId,['id','name']);
            $percentType = EmployeeBasicSalaryTypeEnum::percent->value;
            $fixedType = EmployeeBasicSalaryTypeEnum::fixed->value;
            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId);


            $employeeSalaryGroup = $this->salaryGroupEmployeeRepository->getSalaryGroupFromEmployeeId($employeeId);

            if($employeeSalaryGroup)
            {
                $salaryGroup = $this->salaryGroupService->findOrFailSalaryGroupDetailById($employeeSalaryGroup->salary_group_id, ['*'], ['salaryComponents']);

                if($salaryGroup)
                {
                    $salaryComponents = $salaryGroup->salaryComponents->toArray();
                }
            }
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();

            return view($this->view.'edit_salary', compact('employee','employeeSalary','percentType','fixedType','salaryComponents','currency'));
        }catch(Exception $exception){
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @param $employeeId
     * @return RedirectResponse
     */
    public function updateSalary(Request $request, $employeeId): RedirectResponse
    {
        try{
            $this->authorize('edit_salary');
            $validatedData = $request->all();

            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId);

            $validatedData['weekly_basic_salary'] = round(($validatedData['annual_basic_salary']/52),2);
            $validatedData['weekly_fixed_allowance'] = round(($validatedData['annual_fixed_allowance']/52),2);
            DB::beginTransaction();
            $this->employeeSalaryRepository->update($employeeSalary, $validatedData);
            DB::commit();

            return redirect()->route('admin.employee-salaries.index')->with('success',__('message.salary_update'));


        }catch(Exception $exception){
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }


    public function printPayslip($employeePayslipId): Factory|View|RedirectResponse|Application
    {
        try{

            $this->authorize('print_payroll');
            $companyLogoPath = Company::UPLOAD_PATH;
            $payrollData = $this->generatePayrollService->getEmployeeAccountDetailToCreatePayslip($employeePayslipId);
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $underTimeSetting = $this->utSettingService->getAllUTList(['is_active'],1);
            $numberToWords = new NumberToWords();

            return view($this->view.'print_payslip', compact( 'payrollData','companyLogoPath','currency','underTimeSetting','numberToWords'));
        }catch(Exception $exception){
            return redirect()->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function makePayment($payslipId, Request $request)
    {
        try{

            $this->authorize('payroll_payment');
            $validator = Validator::make($request->all(), [
                'paid_on' => ['required'],
                'payment_method_id' => ['required'],
            ],
            [
                'paid_on.required' => __('message.payment_date_error'),
                'payment_method_id.required' => __('message.payment_method_error'),
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()]);
            }



            $validatedData = $validator->validated();
            $employeePayslipData = [
                "paid_on" => $validatedData['paid_on'],
                "status" => PayslipStatusEnum::paid->value,
                "payment_method_id" => $validatedData['payment_method_id'],
            ];

            $employeePaySlipDetail = $this->payslipRepository->find($payslipId);

            $payslipYearMonth = AppHelper::getYearMonth($employeePaySlipDetail->salary_from);
            $payslipYear = $payslipYearMonth['year'];
            $payslipMonth = $payslipYearMonth['month'];
            $isBsEnabled = AppHelper::ifDateInBsEnabled();
            if($isBsEnabled){
                $currentYearMonth = AppHelper::getCurrentYearMonth();
                $currentYear = $currentYearMonth['year'];
                $currentMonth = $currentYearMonth['month'];
            }else{
                $currentYear = date('Y');
                $currentMonth = date('m');
            }


            if($payslipYear > $currentYear || ($currentYear == $payslipYear && $payslipMonth >= $currentMonth)){
                throw new Exception(__('message.payslip_payment_error'),400);
            }
            DB::beginTransaction();

            if($employeePaySlipDetail->tada > 0){
                $updateData = [
                    'is_settled'=>1,
                    'remark'=>'included in salary.',
                    'verified_by'=>auth()->user()?->id,
                ];
                $this->tadaService->makeSettlement($updateData, $employeePaySlipDetail);
            }

            // make advance salary settlement
            if($employeePaySlipDetail->advance_salary > 0){
                $advanceData = [
                    'is_settled'=>true,
                    'remark' => 'settled in payroll'
                ];
                $this->advanceSalaryService->advanceSalarySettlement($employeePaySlipDetail, $advanceData);
            }

            // make loan payment
            if($employeePaySlipDetail->loan_amount > 0){
                $this->repaymentService->loanSettlement($employeePaySlipDetail,$validatedData['paid_on']);
            }


            $this->payslipRepository->update($employeePaySlipDetail, $employeePayslipData);

            DB::commit();

            $message = "Your salary has been paid on " . $validatedData['paid_on'] . ". Any pending advances have been settled.";
            SMPushHelper::sendSalaryNotification(__('message.salary_notification'), $message,$employeePaySlipDetail->employee_id);
            return response()->json(['success' => true]);
        }catch(Exception $exception){
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param $employeeId
     * @return RedirectResponse
     */
    public function deleteSalary($employeeId): RedirectResponse
    {
        try{
            $this->authorize('add_salary');


            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($employeeId);

            DB::beginTransaction();
                $this->employeeSalaryRepository->delete($employeeSalary);
            DB::commit();

            return redirect()->route('admin.employee-salaries.index')->with('success',__('message.salary_delete'));


        }catch(Exception $exception){
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function getWeeks($year)
    {
        try{
            $weeks = AppHelper::getweeksList($year);

            return response()->json(['success' => true,'data'=>$weeks]);

        }catch(Exception $exception){
            DB::rollBack();
            return redirect()
                ->back()
                ->with('danger', $exception->getMessage());
        }
    }

    public function calculateTax(Request $request){
        try{
            $maritalStatus = $request->query('marital_status');
            $annualIncome = $request->query('salary');

            $data = PayrollHelper::salaryTDSCalculator($maritalStatus, $annualIncome);

            return response()->json(['success' => true,'data'=>$data]);

        }catch(Exception $exception){
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

    public function getEmployeeSalaryData($type)
    {
        try {

            $users = $this->employeeSalaryRepository->getPayrollTypeEmployee($type);

            return response()->json([
                'users' => $users,
            ],200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }
    public function getEmployeeByDepartmentPayrollType(Request $request)
    {
        try {
            $departmentIds = $request->input('department_ids');
            $payrollType = $request->input('payroll_type');
            $users = $this->employeeSalaryRepository->getDPayrollTypeDepartmentEmployee($departmentIds,$payrollType);

            return response()->json([
                'users' => $users,
            ],200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        }
    }

}

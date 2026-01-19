<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Repositories\CompanyRepository;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\UserRepository;
use App\Services\FiscalYear\FiscalYearService;
use App\Services\Payroll\BonusService;
use App\Services\Payroll\EmployeePayslipService;
use App\Services\Payroll\GeneratePayrollService;
use App\Services\Payroll\SalaryComponentService;
use App\Services\Payroll\SalaryTDSService;
use App\Services\TaxReport\TaxReportAdditionalDetailService;
use App\Services\TaxReport\TaxReportBonusDetailService;
use App\Services\TaxReport\TaxReportService;
use App\Services\TaxReport\TaxReportTdsDetailService;
use App\Traits\CustomAuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaxReportController extends Controller
{
    use CustomAuthorizesRequests;
    public $view = 'admin.payroll.taxReport.';

    public function __construct(
        protected UserRepository $userRepository,
        protected GeneratePayrollService $generatePayrollService,
        protected EmployeeSalaryRepository $employeeSalaryRepository,
        protected SalaryComponentService $salaryComponentService,
        protected SalaryTDSService $salaryTDSService,
        protected BonusService $bonusService,
        protected FiscalYearService $fiscalYearService,
        protected EmployeePayslipService $employeePayslipService,
        protected TaxReportService $reportService,
        protected TaxReportAdditionalDetailService $additionalDetailService,
        protected TaxReportTdsDetailService $tdsDetailService,
        protected CompanyRepository $companyRepository
    ){}

    public function index(Request $request)
    {
        if($request->all()){
            $validator = Validator::make($request->all(), [
                'year' => ['required'],
                'employee_id'=>['nullable'],
                'branch_id'=>['required'],
                'department_id'=>['required'],
                'include_ssf'=>['nullable'],

            ]);
            if ($validator->fails()) {
                return redirect()->back() ->withErrors($validator);
            }

            $filterData = $validator->validated();

            $filterData['include_ssf'] = $filterData['include_ssf'] ?? 0;


//            $reportData = $this->reportService->findTaxReportByEmployee( $filterData['employee_id'],  $filterData['year']);

//            if(!$reportData){
                $reportData = $this->saveTaxReport($filterData,  $filterData['year']);

//            }

        }else {

            $reportData = $this->reportService->getAllTaxReport();
            $filterData = [];
        }
        $with = ['branches:id,name'];
        $select = ['id', 'name'];
        $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
        $employees = $this->userRepository->getAllVerifiedEmployeesExceptAdminOfCompany(['id','name']);
        $fiscalYears = $this->fiscalYearService->getAllFiscalYears();
        return view($this->view.'index', compact('fiscalYears','employees', 'reportData','filterData','companyDetail'));

    }


    public function taxReport($id)
    {
        try {
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $months = AppHelper::getMonthsList();
            $taxData = $this->salaryTDSService->getAllSalaryTDSListGroupByMaritalStatus();
            $with = ['employee:id,name,joining_date,marital_status','fiscalYear','additionalDetail.salaryComponent','bonusDetail.bonus:id,title','tdsDetail','componentDetail.salaryComponent','reportDetail'];
            $select = ['*'];
            $reportData = $this->reportService->findTaxReportById($id, $select, $with);

            return view($this->view.'tax_report', compact('months','currency','taxData','reportData'));

        }catch (Exception $exception){
            return redirect()->back()->with('danger', __('message.something_went_wrong'));
        }

    }

    public function printTaxReport($id)
    {
        try {
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $months = AppHelper::getMonthsList();
            $taxData = $this->salaryTDSService->getAllSalaryTDSListGroupByMaritalStatus();
            $with = ['employee:id,name,joining_date,marital_status','fiscalYear','additionalDetail.salaryComponent','bonusDetail.bonus:id,title','tdsDetail','componentDetail.salaryComponent','reportDetail'];
            $select = ['*'];
            $reportData = $this->reportService->findTaxReportById($id, $select, $with);


            $pdf = Pdf::loadView($this->view.'print_tax_report', compact('months','currency','taxData','reportData'))->setPaper('a4', 'landscape');;
            return $pdf->stream('tax_report.pdf');

        }catch (Exception $exception){
            Log::error('PDF generation failed: ' . $exception->getMessage());
            return redirect()->back()->with('danger', __('message.something_went_wrong'));
        }

    }

    public function saveTaxReport($filterData,  $year)
    {
        try {
            DB::beginTransaction();
            $data = $this->reportService->storeTaxReport($filterData,  $year);

            DB::commit();
            return $data;

        }catch (Exception $exception){
            DB::rollBack();
            return [];
        }

    }

    public function deleteTaxReport($taxReportId)
    {
        try {
            DB::beginTransaction();
            $this->reportService->deleteTaxReportById($taxReportId);

            DB::commit();
            return redirect()->back()->with('success', __('message.tax_report_remove'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }

    }

    public function editTaxReport($id)
    {
        try {

            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $months = AppHelper::getMonthsList();
            $taxData = $this->salaryTDSService->getAllSalaryTDSListGroupByMaritalStatus();
            $with = ['employee:id,name,joining_date,marital_status','fiscalYear','additionalDetail.salaryComponent','bonusDetail.bonus:id,title','tdsDetail','componentDetail.salaryComponent','reportDetail'];
            $select = ['*'];
            $reportData = $this->reportService->findTaxReportById($id, $select, $with);

            return view($this->view.'edit_tax_report', compact('months','currency','taxData','reportData'));

        }catch (Exception $exception){
            return redirect()->back()->with('danger', __('message.something_went_wrong'));
        }

    }

    public function updateTaxReport($id, Request $request)
    {
        try {

            $validatedData = $request->all();

            $additionalData = $validatedData['other_component'];
            $tdsData = $validatedData['tds_paid'];

            $paidTds = array_sum($tdsData);

            $reportData = [
                "total_paid_tds" => $paidTds,
                "medical_claim" => $validatedData['medical_claim'],
                "female_discount" => $validatedData['female_discount'],
                "other_discount" => $validatedData['other_discount'],
            ];
            $this->reportService->updateTaxReport($id,$reportData);

            $this->additionalDetailService->updateAdditionalDetail($additionalData);

            $this->tdsDetailService->updateTdsDetail($id, $tdsData);
            return redirect()->route('admin.payroll.tax-report.index')->with('success',__('message.tax_report_add'));

        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger',$exception->getMessage());
        }

    }

}

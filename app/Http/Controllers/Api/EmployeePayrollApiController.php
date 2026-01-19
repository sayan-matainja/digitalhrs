<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\NepaliDate;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Repositories\EmployeePayslipAdditionalRepository;
use App\Requests\Payroll\Payslip\PayslipRequest;
use App\Resources\Payroll\Payslip\PayrollCollection;
use App\Resources\Payroll\Payslip\PayrollResource;
use App\Resources\Payroll\Payslip\PayslipResource;
use App\Resources\Payroll\Payslip\SSFHistoryCollection;
use App\Services\Payroll\GeneratePayrollService;
use App\Services\Payroll\UnderTimeSettingService;
use App\Traits\CustomAuthorizesRequests;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use MilanTarami\NumberToWordsConverter\Services\NumberToWords;

class EmployeePayrollApiController extends Controller
{
    use CustomAuthorizesRequests;
    public function __construct(protected GeneratePayrollService $generatePayrollService, protected UnderTimeSettingService $utSettingService
    ,protected EmployeePayslipAdditionalRepository $additionalRepository){}

    /**
     * @throws AuthorizationException
     */
    public function getPayrollList(PayslipRequest $request): JsonResponse
    {
        $this->authorize('view_payslip_list');

        try {


            $validatedData = $request->validated();
            $isBsEnabled = AppHelper::ifDateInBsEnabled();

            $userId = getAuthUserCode();

            if(!empty($validatedData['year']) && !empty($validatedData['month'])){
                if($isBsEnabled)
                {
                    $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($validatedData['year'], $validatedData['month']);

                    $startDate = date('Y-m-d',strtotime($dateInAD['start_date'])) ?? null;
                    $endDate = date('Y-m-d',strtotime($dateInAD['end_date'])) ?? null;
                }else{
                    $firstDayOfMonth  = Carbon::create($validatedData['year'], $validatedData['month'], 1)->startOfDay();
                    $startDate = date('Y-m-d',strtotime($firstDayOfMonth));
                    $endDate = date('Y-m-d',strtotime($firstDayOfMonth->endOfMonth()));
                }
                $payslip = $this->generatePayrollService->getEmployeePayslip($userId, $startDate, $endDate, $isBsEnabled);
                $payslipData = new PayrollCollection($payslip);
            }
            elseif(!empty($validatedData['year'])){
                if($isBsEnabled)
                {
                    $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($validatedData['year']);

                    $startDate = date('Y-m-d',strtotime($dateInAD['start_date'])) ?? null;
                    $endDate = date('Y-m-d',strtotime($dateInAD['end_date'])) ?? null;
                }else{
                    $firstDayOfMonth  = Carbon::create($validatedData['year'], 1, 1)->startOfDay();
                    $startDate = date('Y-m-d',strtotime($firstDayOfMonth));
                    $endDate = date('Y-m-d',strtotime(date($validatedData['year'].'-12-31')));
                }
                $payslip = $this->generatePayrollService->getEmployeePayslip($userId, $startDate, $endDate, $isBsEnabled);
                $payslipData = new PayrollCollection($payslip);
            }else{

                $payslip = $this->generatePayrollService->getPaidEmployeePayslip($userId,$isBsEnabled);

                $payslipData = new PayrollCollection($payslip);
            }



            $currency = AppHelper::getCompanyPaymentCurrencySymbol();



            $data =[
                'payslip'=>$payslipData,
                'currency'=>$currency
            ];
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function getEmployeePayslipDetailById($id)
    {
        $this->authorize('view_payslip_detail');
        try {


            $payrollData = $this->generatePayrollService->getEmployeePayslipDetail($id);

            $components = $this->generatePayrollService->getEmployeePayslipDetailData($id);
            $earnings = array_values(array_filter($components, function ($component) {
                return $component['component_type'] == 'earning';
            }));

            $deductions = array_values(array_filter($components, function ($component) {
                return $component['component_type'] == 'deductions';
            }));

            $additionalData = $this->additionalRepository->getAll($id);
            $additionalComponent= $additionalData->toArray();
            $additionalEarnings = array_values(array_filter($additionalComponent, function ($component) {
                return $component['component_type'] == 'earning';
            }));

            $additionalDeductions = array_values(array_filter($additionalComponent, function ($component) {
                return $component['component_type'] == 'deductions';
            }));




            /** for pdf view */
            $companyLogoPath = Company::UPLOAD_PATH;
            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $underTimeSetting = $this->utSettingService->getAllUTList(['is_active'],1);
            $numberToWords = new NumberToWords();

            $html = View::make('admin.payroll.employeeSalary.download_payslip', compact('payrollData','additionalEarnings','additionalDeductions','earnings','deductions', 'underTimeSetting','numberToWords', 'companyLogoPath','currency'))->render();

            $mergeEarning = array_merge($earnings,$additionalEarnings);
            $mergeDeductions = array_merge($deductions,$additionalDeductions);

            /** resource for payslip data */
            $payslipDetailData = new PayslipResource($payrollData);

            $data =[
                'payslipData'=>$payslipDetailData,
                'currency'=>$currency,
                'earnings'=>$mergeEarning,
                'deductions'=>$mergeDeductions,
                'file'=>$html,
            ];


            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function ssfHistory(Request $request)
    {
        $this->authorize('ssf_history');
        try {

            $employeeId = getAuthUserCode();
            if ($request->all()) {
                $filterParameter = [
                    'year' => $request->year ?? null,
                ];

                $isBsEnabled = AppHelper::ifDateInBsEnabled();
                if($isBsEnabled)
                {
                    $startMonth = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], 01);

                    $startDate = date('Y-m-d',strtotime($startMonth['start_date'])) ?? null;
                    $endMonth = AppHelper::findAdDatesFromNepaliMonthAndYear($filterParameter['year'], 12);

                    $endDate = date('Y-m-d',strtotime($endMonth['end_date'])) ?? null;
                }else{
                    $startDate = $filterParameter['year'] . '-01-01';
                    $endDate = $filterParameter['year'] . '-12-31';
                }


                $ssfHistory = $this->generatePayrollService->getEmployeeSsfHistory($employeeId, $startDate, $endDate);
            } else {
                $ssfHistory = $this->generatePayrollService->getRecentEmployeeSsf($employeeId);
            }

            $currency = AppHelper::getCompanyPaymentCurrencySymbol();
            $ssfData = new SSFHistoryCollection($ssfHistory);


            $data =[
                'currency'=>$currency,
                'history'=>$ssfData,
            ];
            return AppHelper::sendSuccessResponse(__('index.data_found'),$data);

        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}

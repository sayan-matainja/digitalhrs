<?php

namespace App\Services\TaxReport;

use App\Enum\BonusTypeEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\PayrollHelper;
use App\Repositories\EmployeePayslipDetailRepository;
use App\Repositories\EmployeePayslipRepository;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\SalaryGroupRepository;
use App\Repositories\TaxReportRepository;
use App\Repositories\UserRepository;
use App\Services\FiscalYear\FiscalYearService;
use App\Services\Payroll\BonusService;
use App\Services\Payroll\PFService;
use App\Services\Payroll\SalaryComponentService;
use App\Services\Payroll\SalaryReviseHistoryService;
use App\Services\Payroll\SSFService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isEmpty;

class TaxReportService
{
    public function __construct(protected UserRepository $userRepo, protected SalaryGroupRepository $groupRepository,
                                 protected EmployeePayslipRepository $payslipRepository,
                                protected EmployeePayslipDetailRepository $payslipDetailRepository, protected EmployeeSalaryRepository $employeeSalaryRepository,
                                protected SSFService $ssfService, protected BonusService $bonusService, protected SalaryComponentService $salaryComponentService,
                                protected FiscalYearService $fiscalYearService, protected TaxReportRepository $reportRepository,
                                protected TaxReportComponentDetailService $componentDetailService, protected TaxReportAdditionalDetailService $additionalDetailService,
                                protected TaxReportBonusDetailService $bonusDetailService, protected TaxReportTdsDetailService $tdsDetailService,
                                protected SalaryReviseHistoryService $salaryReviseHistoryService, protected TaxReportDetailService $detailService,
                                protected PFService $pfService){}


    /**
     * @throws Exception
     */
    public function getAllTaxReport()
    {
        return $this->reportRepository->getAll();
    }

    /**
     * @throws Exception
     */
    public function findTaxReportById($id, $select=['*'], $with=[])
    {
        return $this->reportRepository->find($id,$select,$with);
    }
    /**
     * @throws Exception
     */
    public function findTaxReportByEmployee($employeeId, $fiscalYearId)
    {
        return $this->reportRepository->findByEmployee($employeeId, $fiscalYearId);
    }



    public function calculateSalaryComponent($salaryComponents, $annualSalary, $basicSalary,$totalDays): array
    {
        $payslipComponents = [];
        if (count($salaryComponents) > 0) {
            foreach ($salaryComponents as $component) {
                $amount = $this->calculateComponent($component->value_type, $component->annual_component_value, $annualSalary, $basicSalary);

                $daily = $amount /365;
                $monthly = $amount / 12;
                $weekly = $daily * 7;
                $payslipComponents[] = [
                    "id" => $component->id,
                    "name" => $component->name,
                    "type" => $component->component_type,
                    "annual" => $amount,
                    "monthly" => $monthly,
                    "weekly" => $weekly,
                ];

            }
        }
        return $payslipComponents;

    }

    public function calculateComponent($valueType, $annualValue, $annualSalary, $basicSalary): float
    {

        $componentValue = 0;
        if ($valueType == 'fixed') {
            $componentValue = $annualValue;
        } else if ($valueType == 'ctc') {
            $componentValue = ($annualValue / 100) * $annualSalary;
        } else if ($valueType == 'basic') {
            $componentValue = ($annualValue / 100) * $basicSalary;
        }

        return $componentValue;
    }



    /**
     * @throws Exception
     */
    public function storeTaxReport($filterData, $fiscalYearId)
    {


        $allMonths = [];
        $taxReports = [];
        $ssfDetail = [];
        $fiscalYearData = $this->fiscalYearService->findFiscalYearById($fiscalYearId);
        $firstDay = $fiscalYearData->start_date;
        $lastDay = $fiscalYearData->end_date;
        $fiscalYearStartMonth = (int)\App\Helpers\AppHelper::getMonthValue($firstDay);
        $fiscalYearStartYear = (int)date('Y', strtotime($firstDay));
        $fiscalYearEndYear = (int)date('Y', strtotime($lastDay));

        // Generate list of all 12 months in fiscal year
        for ($i = 0; $i < 12; $i++) {
            $allMonths[] = (($fiscalYearStartMonth + $i - 1) % 12) + 1;
        }

        if ($filterData['include_ssf'] == 1) {
            $ssfDetail = $this->ssfService->getSSFDetailForTax($firstDay);
        }
        $pfDetail = $this->pfService->getPFDetailForTax($firstDay);

        $employees = $this->employeeSalaryRepository->getAllEmployeeForTaxReport($filterData);
        $additionalComponents = $this->salaryComponentService->getGeneralSalaryComponents();

        foreach ($employees as $employee) {
            $salaryComponents = [];
            $totals['basic_salary'] = 0;
            $totals['allowance'] = 0;
            $totals['ssf_contribution'] = 0;
            $totals['ssf_deduction'] = 0;
            $totals['payable_tds'] = 0;
            $totals['pf_contribution'] = 0;
            $totals['pf_deduction'] = 0;

            $employeeData = $this->userRepo->getEmployeeAccountDetailsToGeneratePayslip($employee->id);
            $salaryReviseData = $this->salaryReviseHistoryService->getEmployeeSalaryHistory($employee->id);

            if ($employeeData[0]->salary_group_id) {
                $components = $this->groupRepository->findSalaryGroupDetailForPayroll(
                    $employeeData[0]->salary_group_id
                );

                $salaryComponents = $components->salaryComponents;
            }
            if (!isset($employeeData[0]->employee_salary_id)) {
                Log::info('Employee salary data of '.$employeeData[0]->employee_name.' not found');
                continue;
            }


            if (!isset($employeeData[0]->joining_date)) {
                Log::info('Employee joining date of '.$employeeData[0]->employee_name.'  not found');
                continue;
            }

            $joiningTimestamp = strtotime($employeeData[0]->joining_date);



            $workedMonths = 0;
            foreach ($allMonths as $month) {
                $year = ($month >= $fiscalYearStartMonth) ? $fiscalYearStartYear : $fiscalYearEndYear;
                $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                if ($monthStart->timestamp >= $joiningTimestamp) {
                    ++$workedMonths;
                }
            }

            $monthData = [];
            $bonusData = [];
            $taxData = [];
            $taxReportDetailData = [];
            $additionalSalaryComponentData = [];
            $componentData = [];
            foreach ($allMonths as $month) {
                $year = ($month >= $fiscalYearStartMonth) ? $fiscalYearStartYear : $fiscalYearEndYear;
                $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);


                $monthEnd = $monthStart->copy()->endOfMonth();

                if ($monthStart->timestamp < $joiningTimestamp) {
                    continue;
                }

                $useRevise = $salaryReviseData && strtotime($salaryReviseData->date_from) > strtotime($monthEnd);
                $monthlyBasic = $useRevise ? $salaryReviseData->base_monthly_salary : $employeeData[0]->monthly_basic_salary;
                $monthlyAllowance = $useRevise ? $salaryReviseData->base_monthly_allowance : $employeeData[0]->monthly_fixed_allowance;
                $annualSalary = $useRevise ? $salaryReviseData->base_salary : $employeeData[0]->annual_salary;
                $grossMonthly = $annualSalary / 12;

                $ssfDeduction = 0;
                $ssfContribution = 0;
                $monthSalary = $monthlyBasic + $monthlyAllowance;
                if ($filterData['include_ssf'] == 1) {
                    $ssfContribution = $ssfDetail ? ($ssfDetail->office_contribution * $monthlyBasic) / 100 : 0;
                    $ssfDeduction = $ssfDetail ? ($ssfDetail->employee_contribution * $monthlyBasic) / 100 : 0;

                    $monthSalary -= $ssfDeduction;
                }

                $pfDeduction = 0;
                $pfContribution = 0;
                if (isset($pfDetail)) {
                    $pfContribution = isset($pfDetail->office_contribution) ? ($pfDetail->office_contribution * $monthlyBasic) / 100 : 0;
                    $pfDeduction = isset($pfDetail->employee_contribution) ? ($pfDetail->employee_contribution * $monthlyBasic) / 100 : 0;

                    $monthSalary -= $pfDeduction;
                }

                $taxReportDetailData[$month] = [
                    'salary' => $grossMonthly,
                    'basic_salary' => $monthlyBasic,
                    'fixed_allowance' => $monthlyAllowance,
                    'ssf_contribution' => $ssfContribution,
                    'ssf_deduction' => $ssfDeduction,
                    'pf_contribution' => $pfContribution,
                    'pf_deduction' => $pfDeduction,
                ];


                // Salary Group Components

                if(count($salaryComponents) > 0){
                    $employeeComponents = $this->calculateSalaryComponent($salaryComponents, $annualSalary, $monthlyBasic, $totalDays);

                    foreach ($employeeComponents as $component) {


                        $componentData[$month][] = [

                            'salary_component_id' => $component['id'],
                            'type' => $component['type'],
                            'amount' => $component['monthly'],
                        ];
                        $amount = $component['monthly'];

                        if($component['type'] == 'earning') {
                            $monthSalary += $amount;
                        }else{
                            $monthSalary -= $amount;
                        }
                    }


                }


                // Additional Components
                if (count($additionalComponents) > 0) {
                    $additionalSalaryComponents = $this->calculateSalaryComponent($additionalComponents, $annualSalary, $monthlyBasic, $totalDays);
                    foreach ($additionalSalaryComponents as $component) {

                        $additionalSalaryComponentData[$month][] = [
                            'salary_component_id' => $component['id'],
                            'amount' => $component['monthly'],
                        ];


                        if($component['type'] == 'earning') {
                            $monthSalary += $component['monthly'];
                        }else{
                            $monthSalary -= $component['monthly'];
                        }


                    }

                }

                // Tax Calculations

                $taxableIncome = $monthSalary * $workedMonths;

                $taxes = PayrollHelper::salaryTDSCalculator($employeeData[0]->marital_status, $taxableIncome);
                $annualTax = ($ssfDeduction > 0 && $taxes['total_tax'] > $taxes['sst'])
                    ? ($ssfDetail->enable_tax_exemption == 0 ? $taxes['total_tax'] : ($taxes['total_tax'] - $taxes['sst']))
                    : $taxes['total_tax'];
                $monthlyTax = ($ssfDeduction > 0 && $taxes['total_tax'] > $taxes['sst'])
                    ? ($ssfDetail->enable_tax_exemption == 0 ? $taxes['total_tax'] : ($taxes['total_tax'] - $taxes['sst'])) / $workedMonths
                    : $taxes['monthly_tax'];


                // Bonus and Tax on Bonus
                $bonus = $this->bonusCalculator($month, $monthlyBasic, $annualSalary, $employeeData[0]->marital_status, $employee->id);
                $bonusTax = count($bonus) > 0 ? $bonus['tax'] : 0;
                if ($bonusTax > 0) {
                    $bonusData[$month] = $bonus;
                }

                $taxData[$month] = $monthlyTax + $bonusTax;

                // Accumulate Totals
                $totals['basic_salary'] += $monthlyBasic;
                $totals['allowance'] += $monthlyAllowance;
                $totals['ssf_contribution'] += $ssfContribution;
                $totals['ssf_deduction'] += $ssfDeduction;
                $totals['pf_contribution'] += $pfContribution;
                $totals['pf_deduction'] += $pfDeduction;

                if ($ssfDeduction == 0) {
                    $totals['payable_tds'] = $annualTax;
                } else {
                    $totals['payable_tds'] += ($monthlyTax + $bonusTax);
                }

                $monthData[] = $month;
            }

            // Store Main Report
            $reportData = [
                'employee_id' => $employee->id,
                'fiscal_year_id' => $fiscalYearId,
                'total_basic_salary' => $totals['basic_salary'],
                'total_allowance' => $totals['allowance'],
                'total_ssf_contribution' => $totals['ssf_contribution'],
                'total_ssf_deduction' => $totals['ssf_deduction'],
                'total_payable_tds' => $totals['payable_tds'],
                'months' => json_encode($monthData),
                'total_pf_contribution' => $totals['pf_contribution'],
                'total_pf_deduction' => $totals['pf_deduction'],
            ];


            $taxReportData = $this->findTaxReportByEmployee($employee->id, $filterData['year']);

            if (!is_null($taxReportData)) {
                $this->reportRepository->update($taxReportData, $reportData);
                $this->detailService->deleteReportDetail($taxReportData->id);
                $this->componentDetailService->deleteComponentDetail($taxReportData->id);
                $this->additionalDetailService->deleteadditionalDetail($taxReportData->id);
                $this->bonusDetailService->deleteBonusDetail($taxReportData->id);
                $this->tdsDetailService->deleteTdsDetail($taxReportData->id);
            } else {
                $taxReportData = $this->reportRepository->create($reportData);
            }

            if ($taxReportData) {
                if (!empty($taxReportDetailData)) {
                    $this->detailService->store($taxReportData->id, $taxReportDetailData);
                }
                if (count($componentData) > 0) {
                    $this->componentDetailService->store($taxReportData->id, $componentData);
                }
                if (!empty($additionalSalaryComponentData)) {
                    $this->additionalDetailService->store($taxReportData->id, $additionalSalaryComponentData);
                }
                if (!empty($bonusData)) {
                    $this->bonusDetailService->store($taxReportData->id, $bonusData);
                }
                $this->tdsDetailService->store($taxReportData->id, $taxData);
            }

            $taxReports[]= [
                'id' => $taxReportData->id ?? '',
                'name' => $employeeData[0]->employee_name ?? '',
                'year' => $fiscalYearData->year ?? '',
                'total_payable_tds' => $totals['payable_tds'] ?? 0,
            ];
        }

        return $taxReports;
    }


    /**
     * @param $month
     * @param $monthlyBasicSalary
     * @param $annualSalary
     * @param $maritalStatus
     * @return array
     * @throws Exception
     */
    public function bonusCalculator($month, $monthlyBasicSalary, $annualSalary, $maritalStatus,$employeeId): array
    {
        $bonusAmount = 0;
        $bonus = $this->bonusService->findBonusByEmployeeAndMonth($employeeId,$month);

        if(isset($bonus)){

            if ($bonus->value_type == BonusTypeEnum::fixed->value) {
                $bonusAmount = $bonus->value;
            } else if ($bonus->value_type ==  BonusTypeEnum::annual_percent->value) {
                $bonusAmount = ($bonus->value / 100) * $annualSalary;
            } else if ($bonus->value_type ==  BonusTypeEnum::basic_percent->value) {
                $bonusAmount = ($bonus->value / 100) * $monthlyBasicSalary;
            }
            /** Calculate tax for the bonus */
            $bonusTaxableIncome = $bonusAmount * 12; // Tax as if the bonus is annual
            $bonusTaxes = PayrollHelper::salaryTDSCalculator($maritalStatus, $bonusTaxableIncome);

            return [
                'id'=>$bonus->id,
                'title'=>$bonus->title,
                'month'=>$bonus->applicable_month,
                'amount'=>$bonusAmount,
                'tax'=> $bonusTaxes['monthly_tax'],
            ];

        }
        return [];
    }


    /**
     * @throws Exception
     */
    public function updateTaxReport($taxReportId, $validatedData){
        $taxReportData = $this->findTaxReportById($taxReportId);

        $this->reportRepository->update($taxReportData, $validatedData);
    }


    /**
     * @throws Exception
     */
    public function deleteTaxReportById($id)
    {
        $taxReportData = $this->findTaxReportById($id);
        return $this->reportRepository->delete($taxReportData);
    }
}

<?php

namespace App\Services\Payroll;

use App\Enum\BonusTypeEnum;
use App\Enum\PayslipStatusEnum;
use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Helpers\NepaliDate;
use App\Helpers\PayrollHelper;
use App\Models\EmployeePayslipAdditionalDetail;
use App\Models\OverTimeEmployee;
use App\Models\UnderTimeSetting;
use App\Repositories\EmployeePayslipAdditionalRepository;
use App\Repositories\EmployeePayslipDetailRepository;
use App\Repositories\EmployeePayslipRepository;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\SalaryGroupRepository;
use App\Repositories\TadaRepository;
use App\Repositories\UserRepository;
use App\Services\FiscalYear\FiscalYearService;
use App\Services\LoanManagement\LoanRepaymentService;
use App\Services\LoanManagement\LoanService;
use Exception;
use Illuminate\Support\Facades\Log;

class GeneratePayrollService
{
    public function __construct(protected UserRepository $userRepo, protected SalaryGroupRepository $groupRepository,
                                protected TadaRepository $tadaRepository, protected EmployeePayslipRepository $payslipRepository,
                                protected EmployeePayslipDetailRepository $payslipDetailRepository,protected EmployeePayslipAdditionalRepository $additionalRepository,
                                protected EmployeeSalaryRepository $employeeSalaryRepository,
                                protected SSFService $ssfService, protected BonusService $bonusService, protected SalaryComponentService $salaryComponentService,
                                protected FiscalYearService $fiscalYearService, protected SalaryReviseHistoryService $salaryReviseHistoryService,
                                protected AdvanceSalaryService $advanceSalaryService, protected PFService $pfService,
    protected LoanRepaymentService $repaymentService){}

    /**
     * @throws Exception
     */
    public function getEmployeeAccountDetailToCreatePayslip($employeePayslipId): array
    {
        $payslipData = $this->payslipRepository->getAllEmployeePayslipData($employeePayslipId);

        $componentData = $this->payslipDetailRepository->getAll($employeePayslipId);
        $components = $componentData->toArray();
        $earnings = array_values(array_filter($components, function ($component) {
            return $component['component_type'] == 'earning';
        }));

        $deductions = array_values(array_filter($components, function ($component) {
            return $component['component_type'] == 'deductions';
        }));

        $additionalData = $this->additionalRepository->getAdditionalComponents($employeePayslipId);
        $additionalComponent= $additionalData->toArray();
        $additionalEarnings = array_values(array_filter($additionalComponent, function ($component) {
            return $component['component_type'] == 'earning';
        }));

        $additionalDeductions = array_values(array_filter($additionalComponent, function ($component) {
            return $component['component_type'] == 'deductions';
        }));

        return [
            'payslipData'=>$payslipData,
            "earnings"=>$earnings,
            "deductions"=>$deductions,
            "additionalEarnings"=>$additionalEarnings,
            "additionalDeductions"=>$additionalDeductions,
        ];
    }

    /**
     * @throws Exception
     */
    public function getEmployeeSalariesToCreatePayslip($filterData): array
    {
        $isBsEnabled = AppHelper::ifDateInBsEnabled();

        $employeeSalary = [];
        $totalBasicSalary = 0;
        $totalNetSalary = 0;
        $totalAllowance = 0;
        $totalDeduction = 0;
        $otherPayment = 0;
        $totalOverTime = 0;
        $totalUnderTime = 0;
        $currentYear = date('Y');
        $currentMonth = date('m');

        if($filterData['salary_cycle'] == 'weekly'){
            list($startDate, $endDate) = explode(' to ', $filterData['week']);

            $firstDay = date('Y-m-d',strtotime($startDate));
            $lastDay = date('Y-m-d',strtotime($endDate));

            $duration = $firstDay.' to '.$lastDay;

        }else{

            if($isBsEnabled){
                $dateInAD = AppHelper::findAdDatesFromNepaliMonthAndYear($filterData['year'], $filterData['month']);
                $firstDay = date('Y-m-d',strtotime($dateInAD['start_date'])) ?? null;
                $lastDay = date('Y-m-d',strtotime($dateInAD['end_date'])) ?? null;

                $nepaliDate = new NepaliDate();
                $nepaliMonth = $nepaliDate->getNepaliMonth($filterData['month']);
                $duration = $nepaliMonth.' '. $filterData['year'];
                $currentNepaliYearMonth = AppHelper::getCurrentYearMonth();
                $currentYear = $currentNepaliYearMonth['year'];
                $currentMonth = $currentNepaliYearMonth['month'];

            }else{

                $duration = date('F', mktime(0, 0, 0, $filterData['month'], 1)).' '. $filterData['year'];

                $firstDay = date($filterData['year'].'-'.$filterData['month'].'-01');
                $lastDay = date($filterData['year'].'-'. $filterData['month'].'-'.date('t', strtotime($firstDay)));
                $currentYear = date('Y');
                $currentMonth = date('m');
            }
        }

        $enableTaxExemption = AppHelper::enableTaxExemption();
        $employees = $this->employeeSalaryRepository->getAllEmployeeForPayroll($firstDay,$filterData);
        $ssfDetail = $this->ssfService->getSSFDetailForPayroll($firstDay,$lastDay);
        $pfDetail = $this->pfService->getPFDetailForPayroll($firstDay,$lastDay);


        foreach ($employees as $employee){

            $payrollData  = $this->payslipRepository->getEmployeePayslipSummary($employee->employee_id, $firstDay, $lastDay, $isBsEnabled,$filterData);

            if (!isset($payrollData->status) || $payrollData->status == PayslipStatusEnum::generated->value) {
                $employeePayrollData = $this->userRepo->getEmployeeAccountDetailsToGeneratePayslip($employee->employee_id, $filterData);


                if (isset($employeePayrollData[0])) {
                    $salaryReviseData = $this->salaryReviseHistoryService->getEmployeeSalaryHistory($employee->employee_id);



                    /** calculation of payroll if salary cycle is weekly */
                    if($employeePayrollData[0]->salary_cycle == 'weekly') {


                        /** check if attendance data is present, if not don't generate payroll */
                        $attendanceData = AttendanceHelper::getWeeklyDetail($employee->employee_id,$isBsEnabled , $firstDay, $lastDay);
                        if($attendanceData['totalWorkedHourInMin'] == 0){
                            continue;
                        }

                        if(isset($salaryReviseData) && (strtotime($salaryReviseData->date_from) > strtotime($lastDay)) ) {

                            $weeklyBasicSalary = $salaryReviseData->base_weekly_salary;

                            $weeklyAnnualSalary = $salaryReviseData->base_salary;
                            $grossSalary = $weeklySalary = ($weeklyAnnualSalary /52) ;

                            $weeklyHourRate = $salaryReviseData->hour_rate;

                            $weeklyWorkingHours = $salaryReviseData->weekly_hours;

                        }else{
                            $weeklyBasicSalary = $employeePayrollData[0]->weekly_basic_salary;
                            $weeklyAnnualSalary = $employeePayrollData[0]->annual_salary;
                            $grossSalary = $weeklySalary = ($weeklyAnnualSalary / 52);
                            $weeklyHourRate = $employeePayrollData[0]->hour_rate;

                            $weeklyWorkingHours = $employeePayrollData[0]->weekly_hours;

                        }
                        $totalIncome = 0;
                        $total_deduction = 0;

                        /**  ssf data */
                        $ssfDeduction = 0;
                        $ssfContribution = 0;
                        if(($filterData['include_ssf'] == 1) && isset($ssfDetail)){
                            /** office contribution */
                            $ssfContribution = isset($ssfDetail->office_contribution) ? ($ssfDetail->office_contribution * $weeklyBasicSalary)/100 : 0;
                            /** employee Deduction */
                            $ssfDeduction = isset($ssfDetail->employee_contribution) ? ($ssfDetail->employee_contribution * $weeklyBasicSalary)/100 : 0;

                        }
                        $weeklySalary-=$ssfDeduction;

                        /** pg data */
                        $pfDeduction = 0;
                        $pfContribution = 0;
                        if(($filterData['include_pff'] == 1) && isset($pfDetail)){
                            /** office contribution */
                            $pfContribution = isset($pfDetail->office_contribution) ? ($pfDetail->office_contribution * $weeklyBasicSalary)/100 : 0;
                            /** employee Deduction */
                            $pfDeduction = isset($pfDetail->employee_contribution) ? ($pfDetail->employee_contribution * $weeklyBasicSalary)/100 : 0;

                        }
                        $weeklySalary-=$pfDeduction;


                        /** salary tds calculation */
                        $weeklyTax = 0;
                        if ($filterData['include_tds'] == 1) {
                            $taxableIncome = $weeklyAnnualSalary;

                            $taxes = PayrollHelper::salaryTDSCalculator($employeePayrollData[0]->marital_status, $taxableIncome);
                            if($ssfDeduction > 0){
                                $yearlyTax = ($enableTaxExemption == 0 ? $taxes['total_tax'] : ($taxes['total_tax'] - $taxes['sst']));
                                $weeklyTax = $yearlyTax/52;
                            }else{
                                $weeklyTax = $taxes['weekly_tax'];
                            }
                            $weeklySalary -= $weeklyTax;
                        }

                        /** salary components calculation */
                        $employeeSalaryComponents = [];
                        if ($employeePayrollData[0]->salary_group_id) {
                            $components = $this->groupRepository->findSalaryGroupDetailForPayroll($employeePayrollData[0]->salary_group_id);

                            $employeeSalaryComponents = $this->calculateSalaryComponent($components->salaryComponents, $weeklyAnnualSalary,$weeklyBasicSalary);


                            foreach ($employeeSalaryComponents as $component) {

                                if ($component['type'] == 'earning') {
                                    $totalIncome += $component['weekly'];

                                }

                                if ($component['type'] == 'deductions') {
                                    $total_deduction += $component['weekly'];
                                }
                            }

                            $totalAllowance += $totalIncome;
                            $totalDeduction += $total_deduction;

                        }




                        /** attendance data  for payroll calculation */

                        $employeeSalary[$employee->employee_id]['attendanceSummary'] = $attendanceData;

                        $totalAbsentLeaveFee = 0;
                        /** get leave Data */
                        $leaveWiseData = PayrollHelper::getLeaveData($employee->employee_id, $firstDay, $lastDay);

                        $leaveData = $leaveWiseData['leaveTakenByType'];
                        $paidLeaveDays = $leaveData->where('leave_type', 'paid')->sum('total_days');
                        $unpaidLeaveDays = $leaveData->where('leave_type', 'unpaid')->sum('total_days');


                        $weeklyWorkedHours = 0;
                        /** calculate present, absent, leave data for payslip */
                        if (isset($filterData['attendance'])) {

                            $weeklyWorkedHours =  $attendanceData['totalWorkedHourInMin'];

                            $weeklyAbsentHours = $weeklyWorkingHours - ($weeklyWorkedHours/60);
                            $totalAbsentLeaveFee = ($weeklyAbsentHours * $weeklyHourRate);

                            if($totalAbsentLeaveFee > $weeklySalary){
                                $totalAbsentLeaveFee = $weeklySalary;
                                $weeklySalary = 0;
                            }else{
                                $weeklySalary -= $totalAbsentLeaveFee;
                            }

                        }


                        /** overtime calculation */
                        $overTimeEarning =0;
                        $underTimeDeduction =0;
                        $overTime = PayrollHelper::overTimeCalculator($employee->employee_id, $grossSalary);
                        $overWorkHours = 0;
                        $underWorkHours = 0;
                        if($employeePayrollData[0]->payroll_type == 'hourly'){
                            $overWorkHours = ($weeklyWorkedHours/60) - $weeklyWorkingHours;

                            if($overWorkHours > 0){
                                $overTimeEarning = $overWorkHours * $overTime['hourly_rate'];
                            }

                            if($overWorkHours < 0){
                                $overWorkHours = 0;
                            }

                        }

                        $weeklySalary += $overTimeEarning;

                        /** undertime calculation */
//                        $underTimeRate = PayrollHelper::underTimeCalculator($grossSalary);
//                        if ($underTimeRate > 0) {
//                            $totalUnderTime += $attendanceData['totalUnderTime'];
//                        }
//                        $underTimeDeduction = ($attendanceData['totalUnderTime'] / 60) * $underTimeRate;
//                        $weeklySalary -= $underTimeDeduction;


                        /** advance salary adjustment */
                        $totalAdvanceSalary = 0;
                        $advanceSalaryIds = [];
                        if ($filterData['include_advance_salary'] == 1) {
                            $advanceSalary = $this->advanceSalaryService->getEmployeeApprovedAdvanceSalaries($employee->employee_id,$firstDay, 7);
                            $totalAdvanceSalary = $advanceSalary->sum('released_amount'); // Sum of total_expense
                            $advanceSalaryIds = $advanceSalary->pluck('id')->toArray(); // Array of TADA ids
                            $weeklySalary -= $totalAdvanceSalary;
                        }

                        /** tada adjustment */
                        $totalTada = 0;
                        $tadaIds = [];
                        if ($filterData['include_tada'] == 1) {
                            $tada = $this->tadaRepository->getEmployeeUnsettledTadaLists($employee->employee_id,$firstDay, 7);

                            $totalTada = $tada->sum('total_expense'); // Sum of total_expense
                            $tadaIds = $tada->pluck('id')->toArray(); // Array of TADA ids
                            $weeklySalary += $totalTada;
                        }

                        $employeePayslipData = array(
                            "employee_id" => $employee->employee_id,
                            "status" => 'generated',
                            "salary_cycle" => $filterData['salary_cycle'],
                            "salary_from" => $firstDay,
                            "salary_to" => $lastDay,
                            "gross_salary" => $grossSalary,
                            "tds" => $weeklyTax,
                            "advance_salary" => $totalAdvanceSalary,
                            "tada" => $totalTada,
                            "net_salary" => $weeklySalary,
                            "total_days" => 7,
                            "present_days" => $attendanceData['totalPresent'],
                            "absent_days" => $attendanceData['totalAbsent'],
                            "leave_days" => $attendanceData['totalLeave'],
                            "created_by" => auth()->user()->id ?? null,
                            'include_tada' => $filterData['include_tada'],
                            'include_advance_salary' => $filterData['include_advance_salary'],
                            'attendance' => $filterData['attendance'] ?? 0,
                            'absent_paid' => $filterData['absent_paid'] ?? 0,
                            'approved_paid_leaves' => $filterData['approved_paid_leaves'] ?? 0,
                            'absent_deduction' => round($totalAbsentLeaveFee, 2),
                            'weekends' => $attendanceData['totalWeekend'],
                            'holidays' => $attendanceData['totalHoliday'],
                            'paid_leave' => $paidLeaveDays,
                            'unpaid_leave' => $unpaidLeaveDays,
                            'overtime' => $overTimeEarning,
                            'undertime' => $underTimeDeduction,
                            'ssf_contribution'=> $ssfContribution,
                            'ssf_deduction'=>$ssfDeduction,
                            'tada_ids'=> ($filterData['include_tada'] == 1) ? $tadaIds : null,
                            'advance_salary_ids'=> $advanceSalaryIds,
                            'working_hours'=>$weeklyWorkingHours,
                            'worked_hours'=>($weeklyWorkedHours / 60),
                            'overtime_hours'=>$overWorkHours,
                            'undertime_hours'=>$underWorkHours,
                            'hour_rate'=>$weeklyHourRate,
                            'pf_deduction'=>$pfDeduction,
                            'pf_contribution'=>$pfContribution,
                        );

                        $employeePaySlip = $this->payslipRepository->getEmployeePayslipData($employee->employee_id, $firstDay, $lastDay);
                        if ($employeePaySlip) {
                            if ($employeePaySlip->status == PayslipStatusEnum::generated->value) {
                                $this->payslipRepository->update($employeePaySlip, $employeePayslipData);

                                $this->payslipDetailRepository->deleteByPayslipId($employeePaySlip->id);
                            }

                            $employeeSalaryData = $employeePaySlip;

                        } else {
                            $employeeSalaryData = $this->payslipRepository->store($employeePayslipData);

                        }
                        /** add payslip detail data  */
                        if ($employeeSalaryData) {
                            if ($employeeSalaryData->status == PayslipStatusEnum::generated->value) {
                                if (isset($employeeSalaryData->salary_group_id)) {
                                    foreach ($employeeSalaryComponents as $employeeSalaryComponent) {
                                        $employeePayslipDetail = [
                                            'employee_payslip_id' => $employeeSalaryData->id,
                                            'salary_component_id' => $employeeSalaryComponent['id'],
                                            'amount' => ($employeeSalaryData->salary_cycle == 'monthly') ? $employeeSalaryComponent['monthly'] : $employeeSalaryComponent['weekly'],
                                        ];

                                        $this->payslipDetailRepository->store($employeePayslipDetail);

                                    }
                                }
                            }
                        }

                        $employeeSalary[$employee->employee_id] = [
                            'id' => $employeeSalaryData->id,
                            'employee_name' => $employeePayrollData[0]->employee_name,
                            'net_salary' => round($employeeSalaryData->net_salary, 2),
                            'salary_from' => $employeeSalaryData->salary_from,
                            'salary_to' => $employeeSalaryData->salary_to,
                            'status' => $employeeSalaryData->status,
                            'salary_cycle' => $employeeSalaryData->salary_cycle,
                            'working_hours' => $weeklyWorkingHours,
                            'worked_hours' => ($weeklyWorkedHours / 60),
                        ];

                        /** summary data calculation */
                        $totalBasicSalary += $employeePayrollData[0]->monthly_basic_salary;
                        $totalNetSalary += $employeeSalaryData->net_salary;
                    } else {
                        /** payroll Calculation of employee with  monthly salary_cycle  */


                        /** check if attendance data is present, if not don't generate payroll */
                        $attendanceData = AttendanceHelper::getMonthlyDetail($employee->employee_id ,$isBsEnabled ,$filterData['year'] ,$filterData['month']);

                        if($attendanceData['totalPresent'] == 0){
                            continue;
                        }

                        $monthlyHourRate = 0;
                        $monthlyWorkingHours = 0;
                        if (isset($salaryReviseData) && strtotime($salaryReviseData->date_from) > strtotime($lastDay)) {

                            if($employeePayrollData[0]->payroll_type == 'hourly'){
                                $monthlyHourRate = $salaryReviseData->hour_rate;

                                $monthlyWorkingHours = $salaryReviseData->monthly_hours;
                            }
                            $monthlyBasicSalary = $salaryReviseData->base_monthly_salary;

                            $monthlyAnnualSalary = $salaryReviseData->base_salary;

                            $grossSalary = $monthlyAnnualSalary/12;
                            $annualGrossSalary = $monthlyAnnualSalary;
                            $monthlyFixedAllowance = $salaryReviseData->base_monthly_allowance;

                        }else{
                            if($employeePayrollData[0]->payroll_type == 'hourly'){
                                $monthlyHourRate = $employeePayrollData[0]->hour_rate;

                                $monthlyWorkingHours = $employeePayrollData[0]->monthly_hours;
                            }

                            $monthlyBasicSalary = $employeePayrollData[0]->monthly_basic_salary;
                            $monthlyAnnualSalary = $employeePayrollData[0]->annual_salary;

                            $grossSalary = $monthlyAnnualSalary / 12;
                            $annualGrossSalary =  $monthlyAnnualSalary;
                            $monthlyFixedAllowance =  $employeePayrollData[0]->monthly_fixed_allowance;
                        }
                        $monthSalary = $monthlyBasicSalary + $monthlyFixedAllowance;
                        $annualSalary = $monthSalary * 12;


                        $totalIncome = 0;
                        $total_deduction = 0;

                        /** get ssf data */
                        $ssfDeduction = 0;
                        $ssfContribution = 0;
                        if(($filterData['include_ssf'] == 1) && isset($ssfDetail)){

                            /** office contribution */
                            $ssfContribution = isset($ssfDetail->office_contribution) ? ($ssfDetail->office_contribution * $monthlyBasicSalary)/100 : 0;
                            /** employee Deduction */
                            $ssfDeduction = isset($ssfDetail->employee_contribution) ? ($ssfDetail->employee_contribution * $monthlyBasicSalary)/100 : 0;
                        }

                        $monthSalary-=$ssfDeduction;


                        /** pf data */
                        $pfDeduction = 0;
                        $pfContribution = 0;
                        if(($filterData['include_pf'] == 1) && isset($pfDetail)){
                            /** office contribution */
                            $pfContribution = isset($pfDetail->office_contribution) ? ($pfDetail->office_contribution * $monthlyBasicSalary)/100 : 0;
                            /** employee Deduction */
                            $pfDeduction = isset($pfDetail->employee_contribution) ? ($pfDetail->employee_contribution * $monthlyBasicSalary)/100 : 0;

                        }
                        $monthSalary-=$pfDeduction;

                        /** salary components calculation */
                        $employeeSalaryComponents = [];
                        if ($employeePayrollData[0]->salary_group_id) {
                            $components = $this->groupRepository->findSalaryGroupDetailForPayroll($employeePayrollData[0]->salary_group_id);

                            $employeeSalaryComponents = $this->calculateSalaryComponent($components->salaryComponents,$annualGrossSalary, $monthlyBasicSalary);

                            foreach ($employeeSalaryComponents as $component) {

                                if ($component['type'] == 'earning') {
                                    $totalIncome += $component['monthly'];
                                    Log::info('income component '. $component['monthly']);
                                }

                                if ($component['type'] == 'deductions') {
                                    $total_deduction += $component['monthly'];
                                    Log::info('deduction component '. $component['monthly']);
                                }
                            }

                            $totalAllowance += $totalIncome;
                            $totalDeduction += $total_deduction;
                            $monthSalary += $totalIncome;
                            $monthSalary -= $total_deduction;

                        }

                        $additionalSalaryComponents = [];
                        $additionalComponents = $this->salaryComponentService->getGeneralSalaryComponents();

                        if (count($additionalComponents) > 0) {
                            $additionalSalaryComponents = $this->calculateSalaryComponent($additionalComponents,$annualGrossSalary, $monthlyBasicSalary);
                            foreach ($additionalSalaryComponents as $component) {

                                if ($component['type'] == 'deductions') {

                                    $monthSalary -= $component['monthly'];
                                }
                                if ($component['type'] == 'earning') {
                                    $monthSalary += $component['monthly'];
                                }

                            }

                        }

                        /** Bonus Calculation */

                        $bonusTax = 0;
                        $bonusAmount = 0;
                        $bonus = $this->bonusCalculator($filterData['month'], $monthlyBasicSalary, $annualSalary, $employee);

                        if(count($bonus) > 0){
                            $bonusAmount = $bonus['amount'];
                            $monthSalary += $bonusAmount;
                            if ($filterData['include_tds'] == 1) {
                                $bonusTax = $bonus['tax'];
                                $monthSalary -= $bonusTax;
                            }
                        }


                        /** salary tds calculation */
                        $monthlyTax = 0;
                        if ($filterData['include_tds'] == 1) {
                            $taxableIncome = $monthSalary * 12;
                            $taxes = PayrollHelper::salaryTDSCalculator($employeePayrollData[0]->marital_status, $taxableIncome);
                            if($ssfDeduction > 0){
                                $yearlyTax = ($enableTaxExemption == 0 ? $taxes['total_tax'] : ($taxes['total_tax'] - $taxes['sst']));
                                $monthlyTax = $yearlyTax/12;

                            }else{
                                $monthlyTax = $taxes['monthly_tax'];

                            }

                            $monthSalary -= $monthlyTax;
                        }


                        /** get attendance data for payroll calculation */
                        $attendanceData = AttendanceHelper::getMonthlyDetail($employee->employee_id, $isBsEnabled, $filterData['year'], $filterData['month']);
                        $monthlyWorkedHours = $attendanceData['totalWorkedHourInMin'] ?? 0;
                        $employeeSalary[$employee->employee_id]['attendanceSummary'] = $attendanceData;

                        $deductionFee = $grossSalary / $attendanceData['totalDays'];

                        $weekends = AttendanceHelper::countWeekends($firstDay, $lastDay);

                        /**  get leave Data for leave deduction calculation  */
                        $leaveWiseData = PayrollHelper::getLeaveData($employee->employee_id, $firstDay, $lastDay);

                        $leaveData = $leaveWiseData['leaveTakenByType'];
                        $paidLeaveDays = $leaveData->where('leave_type', 'paid')->sum('total_days');
                        $unpaidLeaveDays = $leaveData->where('leave_type', 'unpaid')->sum('total_days');
                        $totalAbsentLeaveFee = 0;
                        $absentDays = 0;
                        if (isset($filterData['attendance'])) {
                            if($employeePayrollData[0]->payroll_type == 'hourly'){

                                $lessWorkHours = $monthlyWorkingHours - ($monthlyWorkedHours/60);

                                $totalAbsentLeaveFee = $lessWorkHours * $monthlyHourRate;
                            }else{
                                if($currentYear == $filterData['year'] && $currentMonth == $filterData['month']){
                                    $absentDays = $attendanceData['totalDays'] - $weekends -$attendanceData['totalPresent']-$attendanceData['totalHoliday']-$attendanceData['totalLeave'];
                                }else{
                                    $absentDays = $attendanceData['totalAbsent'];
                                }

                                $totalAbsentLeaveFee = ($deductionFee * $absentDays) + ($deductionFee * $unpaidLeaveDays);
                            }


                            if($totalAbsentLeaveFee > $monthSalary){
                                $totalAbsentLeaveFee = $monthSalary;
                                $monthSalary = 0;
                            }else{
                                $monthSalary -= $totalAbsentLeaveFee;
                            }

                        }

                        /** adjust absent days as paid */
//                            if ($filterData['absent_paid'] == 1) {
//                                $absentFees = $deductionFee * $attendanceData['totalAbsent'];
//
//                                $monthSalary += $absentFees;
//                            }

                        /** adjust approved leaves as paid */
//                            if ($filterData['approved_paid_leaves'] == 1) {
//                                $leaveFees = $deductionFee * $unpaidLeaveDays;
//
//                                $monthSalary += $leaveFees;
//                            }


                        /** overtime calculation */
                        $overTime = PayrollHelper::overTimeCalculator($employee->employee_id, $grossSalary);
                        $overWorkHours = 0;
                        $underWorkHours = 0;
                        if($employeePayrollData[0]->payroll_type == 'hourly'){
                            $overTimeEarning = 0;
                            $overWorkHours = ($monthlyWorkedHours/60) - $monthlyWorkingHours;

                            if($overWorkHours > 0){
                                $overTimeEarning = $overWorkHours * $overTime['hourly_rate'];
                            }

                        }else{
                            if ($overTime['hourly_rate'] > 0) {
                                $totalOverTime += $attendanceData['totalOverTime'];
                            }

                            if ($attendanceData['totalOverTime'] > $overTime['monthly_limit']) {
                                $overTimeEarning = ($overTime['monthly_limit'] / 60) * $overTime['hourly_rate'];
                            } else {
                                $overTimeEarning = ($attendanceData['totalOverTime'] / 60) * $overTime['hourly_rate'];
                            }
                        }

                        $monthSalary += $overTimeEarning;

                        /** undertime calculation */
                        $underTimeDeduction = 0;
                        if($employeePayrollData[0]->payroll_type == 'annual') {

                            $underTimeRate = PayrollHelper::underTimeCalculator($grossSalary);
                            if ($underTimeRate > 0) {
                                $totalUnderTime += $attendanceData['totalUnderTime'];
                            }
                            $underTimeDeduction = ($attendanceData['totalUnderTime'] / 60) * $underTimeRate;

                            $monthSalary -= $underTimeDeduction;
                        }

                        /** loan calculation */
                        $loanAmount = 0;
                        $loanRepaymentId = null;
                        $loanRepayment = $this->repaymentService->getLoanRepayment($employee->employee_id,$firstDay,$lastDay);

                        if(isset($loanRepayment)){
                            $loanAmount = $loanRepayment->principal_amount + $loanRepayment->interest_amount + $loanRepayment->settlement_amount;
                            if($monthSalary > $loanAmount){
                                $loanRepaymentId = $loanRepayment->id;
                                $monthSalary -= $loanAmount;
                            }
                        }


                        /** advance salary adjustment */
                        $totalAdvanceSalary = 0;
                        $advanceSalaryIds = [];
                        if ($filterData['include_advance_salary'] == 1) {
                            $advanceSalary = $this->advanceSalaryService->getEmployeeApprovedAdvanceSalaries($employee->employee_id,$firstDay,30);
                            $totalAdvanceSalary = $advanceSalary->sum('released_amount'); // Sum of total_expense
                            $advanceSalaryIds = $advanceSalary->pluck('id')->toArray(); // Array of TADA ids
                            $monthSalary -= $totalAdvanceSalary;
                        }


                        /** tada adjustment */
                        $totalTada = 0;
                        $tadaIds = [];
                        if ($filterData['include_tada'] == 1) {
                            $tada = $this->tadaRepository->getEmployeeUnsettledTadaLists($employee->employee_id,$firstDay,30);

                            $totalTada = $tada->sum('total_expense'); // Sum of total_expense
                            $tadaIds = $tada->pluck('id')->toArray(); // Array of TADA ids
                            $monthSalary += $totalTada;
                        }


                        $employeePayslipData = [
                            "employee_id" => $employee->employee_id,
                            "status" => 'generated',
                            "salary_cycle" => $filterData['salary_cycle'],
                            "salary_from" => $firstDay,
                            "salary_to" => $lastDay,
                            "gross_salary" => $grossSalary,
                            "tds" => $monthlyTax + $bonusTax,
                            "advance_salary" => $totalAdvanceSalary,
                            "tada" => $totalTada,
                            "net_salary" => $monthSalary,
                            "total_days" => $attendanceData['totalDays'],
                            "present_days" => $attendanceData['totalPresent'],
                            "absent_days" => $absentDays,
                            "leave_days" => $attendanceData['totalLeave'],
                            "created_by" => auth()->user()->id ?? null,
                            'include_tada' => $filterData['include_tada'],
                            'include_advance_salary' => $filterData['include_advance_salary'],
                            'attendance' => $filterData['attendance'],
                            'absent_paid' => $filterData['absent_paid'] ?? 0,
                            'approved_paid_leaves' => $filterData['approved_paid_leaves'] ?? 0,
                            'absent_deduction' => round($totalAbsentLeaveFee, 2),
                            'weekends' => $weekends,
                            'holidays' => $attendanceData['totalHoliday'],
                            'paid_leave' => $paidLeaveDays,
                            'unpaid_leave' => $unpaidLeaveDays,
                            'overtime' => $overTimeEarning,
                            'undertime' => $underTimeDeduction,
                            'is_bs_enabled' => $isBsEnabled,
                            'ssf_contribution'=>$ssfContribution,
                            'ssf_deduction'=>$ssfDeduction,
                            'bonus'=> $bonusAmount,
                            'tada_ids'=> ($filterData['include_tada'] == 1) ? $tadaIds : null,
                            'advance_salary_ids'=> $filterData['include_advance_salary'] == 1 ? $advanceSalaryIds : null,
                            'working_hours'=>$monthlyWorkingHours,
                            'worked_hours'=>($monthlyWorkedHours / 60),
                            'overtime_hours'=>$overWorkHours,
                            'undertime_hours'=>$underWorkHours,
                            'hour_rate'=>$monthlyHourRate,
                            'pf_deduction'=>$pfDeduction,
                            'pf_contribution'=>$pfContribution,
                            'loan_amount'=>$loanAmount,
                            'loan_repayment_id'=>$loanRepaymentId ?? null,
                        ];

                        $employeePaySlip = $this->payslipRepository->getEmployeePayslipData($employee->employee_id, $firstDay, $lastDay);


                        if ($employeePaySlip) {

                            if ($employeePaySlip->status == PayslipStatusEnum::generated->value) {
                                $this->payslipRepository->update($employeePaySlip, $employeePayslipData);

                                $this->payslipDetailRepository->deleteByPayslipId($employeePaySlip->id);
                                $this->additionalRepository->deleteByPayslipId($employeePaySlip->id);
                            }

                            $employeeSalaryData = $employeePaySlip;

                        } else {
                            $employeeSalaryData = $this->payslipRepository->store($employeePayslipData);

                        }


                        /**  add payslip detail data  */
                        if ($employeeSalaryData) {
                            if ($employeeSalaryData->status == PayslipStatusEnum::generated->value) {
                                if (isset($employeeSalaryData->salary_group_id)) {

                                    foreach ($employeeSalaryComponents as $employeeSalaryComponent) {
                                        $employeePayslipDetail = [
                                            'employee_payslip_id' => $employeeSalaryData->id,
                                            'salary_component_id' => $employeeSalaryComponent['id'],
                                            'amount' => ($employeeSalaryData->salary_cycle == 'monthly') ? $employeeSalaryComponent['monthly'] : $employeeSalaryComponent['weekly'],
                                        ];

                                        $this->payslipDetailRepository->store($employeePayslipDetail);

                                    }
                                }

                                if (count($additionalSalaryComponents) > 0) {
                                    foreach ($additionalSalaryComponents as $employeeSalaryComponent) {
                                        $employeePayslipDetail = [
                                            'employee_payslip_id' => $employeeSalaryData->id,
                                            'salary_component_id' => $employeeSalaryComponent['id'],
                                            'amount' => $employeeSalaryComponent['monthly'],
                                        ];

                                        $this->additionalRepository->store($employeePayslipDetail);

                                    }
                                }

                            }
                        }

                        $employeeSalary[$employee->employee_id] = [
                            'id' => $employeeSalaryData->id,
                            'employee_name' => $employeePayrollData[0]->employee_name,
                            'net_salary' => round($employeeSalaryData->net_salary, 2),
                            'salary_from' => $employeeSalaryData->salary_from,
                            'salary_to' => $employeeSalaryData->salary_to,
                            'status' => $employeeSalaryData->status,
                            'salary_cycle' => $employeeSalaryData->salary_cycle,
                        ];

                        /** summary data */
                        $totalBasicSalary += $employeePayrollData[0]->monthly_basic_salary;
                        $totalNetSalary += $employeeSalaryData->net_salary;
                    }

                }

            } else {

                $payslipDetailData = $this->payslipDetailRepository->getAll($payrollData->id);

                $employeeSalary[$employee->employee_id] = [
                    'id' => $payrollData->id,
                    'employee_name' => $payrollData->employee_name,
                    'net_salary' => $payrollData->net_salary,
                    'salary_from' => $payrollData->salary_from,
                    "salary_to" => $payrollData->salary_to,
                    "paid_on" => $payrollData->paid_on,
                    "status" => $payrollData->status,
                    'paid_by' => $payrollData->paid_by,
                    'salary_cycle' => $payrollData->salary_cycle,
                ];

                //summaryData
                $totalBasicSalary += $payrollData->monthly_basic_salary;
                $totalNetSalary += $payrollData->net_salary;
                foreach ($payslipDetailData as $detail) {
                    if ($detail->component_type == 'earning') {
                        $totalAllowance += $detail->amount;
                    } else {
                        $totalDeduction += $detail->amount;
                    }
                }

            }
        }

        $payrollSummary = [
            'duration'=>$duration,
            'totalBasicSalary'=>round($totalBasicSalary,2),
            'totalNetSalary'=>round($totalNetSalary,2),
            'totalAllowance'=>round($totalAllowance,2),
            'totalDeduction'=>round($totalDeduction,2),
            'otherPayment'=>round($otherPayment,2),
            'totalOverTime'=>round($totalOverTime,2),
            'totalUnderTime'=>round($totalUnderTime,2),
        ];


        usort($employeeSalary, function ($a, $b) {
            if ($a['status'] == $b['status']) {
                return 0;
            }
            return ($a['status'] == 'generated') ? -1 : 1;
        });

        return [
            'payrollSummary'=>$payrollSummary,
            'employeeSalary'=>$employeeSalary
        ];
    }

    public function calculateSalaryComponent($salaryComponents, $annualSalary, $basicSalary): array
    {
        $payslipComponents = [];
        if (count($salaryComponents) > 0) {
            foreach ($salaryComponents as $component) {
                $amount = $this->calculateComponent($component->value_type, $component->annual_component_value, $annualSalary, $basicSalary);

                $annual = $amount * 12;

                $weekly = $annual / 52;

                $payslipComponents[] = [
                    "id" => $component->id,
                    "name" => $component->name,
                    "type" => $component->component_type,
                    "annual" => $annual,
                    "monthly" => $amount,
                    "weekly" => round($weekly, 2),
                ];

            }
        }
        return $payslipComponents;

    }

    public function calculateComponent($valueType, $annualValue, $annualSalary, $basicSalary): float
    {

        $componentValue = 0;
        if ($valueType == 'fixed') {

            $componentValue = $annualValue/12;


        } else if ($valueType == 'ctc') {

            $componentValue = (($annualValue / 100) * $annualSalary)/12;


        } else if ($valueType == 'basic') {

            $componentValue = (($annualValue / 100) * $basicSalary)/12;

        }

        return round($componentValue, 2);
    }

    public function getCurrentEmployeeSalaries(): array
    {
        $totalBasicSalary = 0;

        $totalNetSalary = 0;
        $totalAllowance = 0;
        $totalCommission = 0;
        $totalLoan = 0;
        $totalDeduction = 0;
        $otherPayment = 0;
        $totalOverTime = 0;
        $isBsEnabled = AppHelper::ifDateInBsEnabled();

        if($isBsEnabled){
            $currentNepaliYearMonth = AppHelper::getCurrentYearMonth();
            $year = $currentNepaliYearMonth['year'];
            $month = $currentNepaliYearMonth['month'] - 1;
            if($month == 0){
                $month = 12;
                $year = $currentNepaliYearMonth['year']-1;
            }
            $nepaliDate = new NepaliDate();
            $nepaliMonth = $nepaliDate->getNepaliMonth($month);
            $duration = $nepaliMonth.' '. $year;

            $dateInAd = AppHelper::findAdDatesFromNepaliMonthAndYear($year, $month);

            $firstDay =$dateInAd['start_date'];
            $lastDay =$dateInAd['end_date'];
        }else{
            $firstDay = date('Y-m-01', strtotime('first day of last month'));
            $lastDay = date('Y-m-t', strtotime('last day of last month'));
            $duration = date('F Y', strtotime('last month'));
        }

        $employeeSalary  = $this->payslipRepository->getEmployeeCurrentPayslipList($firstDay, $lastDay, $isBsEnabled);

        $payrollSummary = [
            'totalBasicSalary'=>$totalBasicSalary,
            'duration'=>$duration,
            'totalNetSalary'=>$totalNetSalary,
            'totalAllowance'=>$totalAllowance,
            'totalCommission'=>$totalCommission,
            'totalLoan'=>$totalLoan,
            'totalDeduction'=>$totalDeduction,
            'otherPayment'=>$otherPayment,
            'totalOverTime'=>$totalOverTime,
        ];

        return [
            'payrollSummary'=>$payrollSummary,
            'employeeSalary'=>$employeeSalary
        ];
    }

    public function getEmployeePayslip($employeeId, $startDate, $endDate, $isBsEnabled){
        return $this->payslipRepository->getEmployeePayslipList($employeeId, $startDate, $endDate, $isBsEnabled);
    }

    public function getPaidEmployeePayslip($employeeId, $isBsEnabled){

        return $this->payslipRepository->getPaidEmployeePayslipList($employeeId, $isBsEnabled);
    }

    public function getEmployeePayslipDetail($employeePayslipId)
    {
        return $this->payslipRepository->getAllEmployeePayslipData($employeePayslipId);
    }

    /**
     * @throws Exception
     */
    public function getEmployeePayslipDetailData($employeePayslipId): array
    {
        return $this->payslipDetailRepository->getAll($employeePayslipId)->toArray();
    }

    public function bonusCalculator($month, $monthlyBasicSalary, $annualSalary, $employee): array
    {
        $bonusAmount = 0;
        $maritalStatus = $employee->marital_status;
        $employeeId = $employee->employee_id;

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



    public function getEmployeeSsfHistory($employeeId, $startDate, $endDate){
        return $this->payslipRepository->getEmployeeSsfList($employeeId, $startDate, $endDate);
    }

    public function getRecentEmployeeSsf($employeeId){
        return $this->payslipRepository->getRecentEmployeeSsfList($employeeId);
    }
}

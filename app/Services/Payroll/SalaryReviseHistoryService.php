<?php

namespace App\Services\Payroll;

use App\Helpers\AppHelper;
use App\Repositories\EmployeeSalaryRepository;
use App\Repositories\SalaryReviseHistoryRepository;
use App\Repositories\UserAccountRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryReviseHistoryService
{
    public function __construct(
        public SalaryReviseHistoryRepository $salaryHistoryRepo,
        public EmployeeSalaryRepository $employeeSalaryRepository,
        public SalaryGroupService $salaryGroupService,
    ){}

    public function getEmployeeAllSalaryHistory($employeeId,$select=['*'],$with=[])
    {
        return $this->salaryHistoryRepo->getAllEmployeeSalaryHistoryList($employeeId,$select,$with);
    }
    public function getEmployeeSalaryHistory($employeeId)
    {
        return $this->salaryHistoryRepo->getSalaryHistoryByEmployee($employeeId);
    }

    /**
     * @param $validatedData
     * @return void
     * @throws Exception
     */
    public function store($validatedData): void
    {
        try{

            $employeeSalary = $this->employeeSalaryRepository->getEmployeeSalaryByEmployeeId($validatedData['employee_id']);
            if(!$employeeSalary)
            {
                throw new Exception(__('message.employee_salary_not_found'),404);
            }

            $validatedData['base_salary'] = $employeeSalary->annual_salary;
            $validatedData['base_monthly_salary'] = $employeeSalary->monthly_basic_salary;
            $validatedData['base_weekly_salary'] = $employeeSalary->weekly_basic_salary;
            $validatedData['base_monthly_allowance'] = $employeeSalary->monthly_fixed_allowance;
            $validatedData['base_weekly_allowance'] = $employeeSalary->weekly_fixed_allowance;
            $validatedData['hour_rate'] = $employeeSalary->hour_rate;
            $validatedData['weekly_hours'] = $employeeSalary->weekly_hours;
            $validatedData['monthly_hours'] = $employeeSalary->monthly_hours;
            $validatedData['salary_revised_on'] = AppHelper::getCurrentDateInYmdFormat();

            DB::beginTransaction();
            $salaryReviseHistory = $this->salaryHistoryRepo->store($validatedData);
            if($salaryReviseHistory)
            {
                $monthlyBasic = $employeeSalary->basic_salary_value;

                $monthSalary = ($salaryReviseHistory->revised_salary /12);


                if($employeeSalary->basic_salary_type == 'percent'){
                    $monthlyBasic =  ($employeeSalary->basic_salary_value /100) * $monthSalary;
                }


                $salaryGroup = $this->salaryGroupService->findOrFailSalaryGroupDetailById($employeeSalary->salary_group_id, ['*'], ['salaryComponents']);
                $incomeComponent = 0;
                if(isset($salaryGroup->salaryComponents)){
                    foreach ($salaryGroup->salaryComponents as $component) {

                        if($component->component_type == 'earning'){
                            if($component->value_type == 'basic'){
                                $incomeComponent +=($component->component_value_monthly / 100) *  $monthlyBasic;
                            }elseif($component->value_type == 'ctc'){
                                $incomeComponent +=($component->component_value_monthly / 100) *  $salaryReviseHistory->revised_salary;
                            }else{
                                $incomeComponent +=$component->component_value_monthly;
                            }
                        }

                    }
                }

                $monthlyFixedAllowance = (($monthSalary - $monthlyBasic) - $incomeComponent);

                $employeeSalaryData = [
                    'annual_salary'=> $salaryReviseHistory->revised_salary,
                    'monthly_basic_salary'=>$monthlyBasic,
                    'annual_basic_salary'=> $monthlyBasic * 12,
                    'monthly_fixed_allowance'=> $monthlyFixedAllowance,
                    'annual_fixed_allowance'=>$monthlyFixedAllowance * 12,
                ];

                if(!is_null($employeeSalary->hour_rate)){
                    $weeklyWorkingHour = $employeeSalary->weekly_hours;

                    $employeeSalaryData['hour_rate'] = ($salaryReviseHistory->revised_salary/52) / $weeklyWorkingHour;
                    $employeeSalaryData['weekly_basic_salary'] = ($monthlyBasic * 12)/52;
                    $employeeSalaryData['weekly_fixed_allowance'] = ($monthlyFixedAllowance * 12)/52;
                }


                $this->employeeSalaryRepository->update($employeeSalary, $employeeSalaryData);
            }
            DB::commit();
            return ;
        }catch (Exception $exception){
            DB::rollBack();
        }
    }
}

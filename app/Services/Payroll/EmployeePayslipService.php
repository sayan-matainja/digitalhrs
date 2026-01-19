<?php

namespace App\Services\Payroll;

use App\Repositories\EmployeePayslipRepository;
use App\Services\FiscalYear\FiscalYearService;
use Exception;
use Illuminate\Support\Facades\DB;

class EmployeePayslipService
{
    public function __construct(protected EmployeePayslipRepository $employeePayslipRepo, protected FiscalYearService $fiscalYearService){}

    /**
     * @throws Exception
     */
    public function getPayslipDetailByEmployeeId($employeeId, $firstDay, $lastDay)
    {
        return $this->employeePayslipRepo->getEmployeePayslipDataByFiscalYear($employeeId, $firstDay, $lastDay);
    }





}

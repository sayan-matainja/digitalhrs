<?php

namespace App\Repositories;

use App\Enum\LoanRepaymentStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Helpers\AppHelper;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Traits\ImageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class LoanRepaymentRepository
{
    use ImageService;


    public function getAllRepaymentsPaginated($filterParameters, $select = ['*'], $with = [])
    {
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;

        LoanRepayment::whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->where('status',LoanRepaymentStatusEnum::upcoming->value)
            ->update(['status' => 'active']);

        return Loan::select($select)
            ->with($with)
            ->when($filterParameters['branch_id'] ?? null, function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when($filterParameters['department_id'] ?? null, function ($query) use ($filterParameters) {
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when($filterParameters['employee_id'] ?? null, function ($query) use ($filterParameters) {
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->when($filterParameters['type_id'] ?? null, function ($query) use ($filterParameters) {
                $query->where('loan_type_id', $filterParameters['type_id']);
            })
            ->latest()
            ->paginate(getRecordPerPage());
    }

    public function getAllRepayments($loanId, $select = ['*'], $with = [])
    {
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;


        LoanRepayment::whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->where('status',LoanRepaymentStatusEnum::upcoming->value)
            ->update(['status' => 'active']);



        return LoanRepayment::select($select)->with($with)
            ->where('loan_id', $loanId)
            ->orderBy('payment_date', 'asc')
            ->get();
    }

    public function findLoanRepayment($employeeId,$startDate,$endDate, $select = ['*'], $with = [])
    {
        return LoanRepayment::select($select)
            ->with($with)
            ->where('employee_id', $employeeId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->where('is_paid',0)
            ->first();
    }


    public function getRepaymentById($loanRepaymentId, $select = ['*'], $with = [])
    {
        return LoanRepayment::select($select)->with($with)
            ->where('id', $loanRepaymentId)
            ->first();
    }
    public function settleRepayment($repaymentDetail,$paidDate)
    {

        return $repaymentDetail->update(
            [
                'is_paid'=>1,
                'paid_date'=>$paidDate,
                'status'=>LoanRepaymentStatusEnum::paid->value,
            ]
        );
    }
    public function update($repaymentDetail,$validatedData)
    {

        return $repaymentDetail->update($validatedData);
    }

    public function getRecentInstallment($select =['*'],$with=[])
    {
        $dates = AppHelper::getCurrentMonthDates();
        return LoanRepayment::select($select)
            ->with($with)
            ->where('employee_id', getAuthUserCode())
            ->whereBetween('payment_date',[$dates['start_date'],$dates['end_date']])
            ->where('status','=',LoanRepaymentStatusEnum::active->value)
            ->first();
    }

    public function getEmployeeRecentInstallment($employeeId,$select =['*'],$with=[])
    {
        $dateFormatted = Carbon::now()->format('Y-m');
        return LoanRepayment::select($select)
            ->with($with)
            ->where('employee_id', $employeeId)
            ->whereRaw("DATE_FORMAT(payment_date, '%Y-%m') = ?", [$dateFormatted])
            ->orWhere('status','=',LoanRepaymentStatusEnum::active->value)
            ->first();
    }

    public function getPastMonthInstallment($loanRepayment,$select =['*'],$with=[])
    {

        return LoanRepayment::select($select)->with($with)->where('loan_id', $loanRepayment->loan_id)
            ->where('payment_date', '<', $loanRepayment->payment_date)
            ->orderBy('payment_date', 'desc')
            ->first();
    }



}

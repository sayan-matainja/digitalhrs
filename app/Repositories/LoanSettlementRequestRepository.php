<?php

namespace App\Repositories;

use App\Enum\LoanStatusEnum;
use App\Models\LoanSettlementRequest;
use App\Traits\ImageService;
use Carbon\Carbon;


class LoanSettlementRequestRepository
{
    use ImageService;

    public function getAllSettlementRequest($filterParameters,$select=['*'],$with=[])
    {
        return LoanSettlementRequest::select($select)->with($with)

             ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
              ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function($query) use ($filterParameters){
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->when(isset($filterParameters['loan_id']) && $filterParameters['loan_id'] != 'all', function($query) use ($filterParameters){
                $query->where('loan_id', $filterParameters['loan_id']);
            })
            ->when(isset($filterParameters['status']) && $filterParameters['status'] != 'all', function($query) use ($filterParameters){
                $query->where('status', $filterParameters['status']);
            })

            ->latest()
            ->paginate( getRecordPerPage());
    }


    public function findSettlementRequestById($id,$select=['*'],$with=[])
    {
        return LoanSettlementRequest::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function store($validatedData)
    {
        return LoanSettlementRequest::create($validatedData)->fresh();
    }

    public function update($settlementRequestDetail,$validatedData)
    {

        return $settlementRequestDetail->update($validatedData);
    }

    public function delete($settlementRequestDetail)
    {
        return $settlementRequestDetail->delete();
    }

    public function checkPendingLoanSettlementRequest($employeeId)
    {
        return LoanSettlementRequest::where('employee_id', $employeeId)
            ->where('status', LoanStatusEnum::pending->value)
            ->exists();
    }
    public function getSettlementRequestAmount($employeeId)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;

        return LoanSettlementRequest::where('employee_id', $employeeId)
            ->where('status','!=', LoanStatusEnum::reject->value)
            ->where('settlement_method','=','salary')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');
    }



}

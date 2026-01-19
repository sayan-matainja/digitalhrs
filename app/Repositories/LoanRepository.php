<?php

namespace App\Repositories;

use App\Enum\LoanRepaymentStatusEnum;
use App\Enum\LoanStatusEnum;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Traits\ImageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class LoanRepository
{
    use ImageService;

    public function getAllLoansPaginated($filterParameters,$select=['*'],$with=[])
    {
        Loan::settled()->update(['status' => 'settled']);

        return Loan::select($select)->with($with)
            ->when(isset($filterParameters['type_id']), function($query) use ($filterParameters){
                $query->where('loan_type_id', $filterParameters['type_id']);
            })
             ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
              ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function($query) use ($filterParameters){
                $query->where('employee_id', $filterParameters['employee_id']);
            })
//            ->when(isset($filterParameters['status']) && $filterParameters['status'] != 'all', function($query) use ($filterParameters){
//                $query->where('status', $filterParameters['status']);
//            })
            ->where('status','!=',LoanStatusEnum::settled->value)

            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function getApiLoansPaginated($select=['*'],$with=[])
    {
        Loan::settled()->update(['status' => 'settled']);

        return Loan::select($select)->with($with)
            ->where('employee_id', getAuthUserCode())
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function getSettledLoansPaginated($select=['*'],$with=[])
    {
        return Loan::select($select)->with($with)
            ->where('status',LoanStatusEnum::settled->value)
            ->latest()
            ->paginate( getRecordPerPage());
    }


    public function findLoanById($id,$select=['*'],$with=[])
    {
        return Loan::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function find($loanId,$select=['*'],$with=[])
    {
        return Loan::select($select)
            ->with($with)
            ->where('status',LoanStatusEnum::approve->value)
            ->where('id',$loanId)
            ->whereHas('loanRepayment', function($repayQuery) {
                $repayQuery->whereIn('status', [LoanRepaymentStatusEnum::active->value, LoanRepaymentStatusEnum::upcoming->value]);
            })
            ->first();
    }

    public function findLoanByEmployeeId($employeeId,$select=['*'],$with=[])
    {
        return Loan::select($select)
            ->with($with)
            ->where('status',LoanStatusEnum::approve->value)
            ->where('employee_id',$employeeId)
            ->whereHas('loanRepayment', function($repayQuery) {
                $repayQuery->whereIn('status', [LoanRepaymentStatusEnum::active->value, LoanRepaymentStatusEnum::upcoming->value]);
            })
            ->first();
    }

    public function store($validatedData)
    {
        if(isset($validatedData['attachment'])){
            $validatedData['attachment'] = $this->storeImage($validatedData['attachment'], Loan::UPLOAD_PATH,500,500);

        }
        $validatedData['created_by'] = Auth::check() ? Auth::user()->id : null;
        return Loan::create($validatedData)->fresh();
    }

    public function update($loanDetail,$validatedData)
    {
        if (isset($validatedData['attachment'])) {
            if($loanDetail['attachment']){
                $this->removeImage(Loan::UPLOAD_PATH, $loanDetail['attachment']);
            }
            $validatedData['attachment'] = $this->storeImage($validatedData['attachment'], Loan::UPLOAD_PATH,500,500);
        }
        $validatedData['updated_by'] = Auth::check() ? Auth::user()->id : null;

        return $loanDetail->update($validatedData);
    }

    public function delete($loanDetail)
    {

        if($loanDetail['attachment']){
            $this->removeImage(Loan::UPLOAD_PATH, $loanDetail['attachment']);
        }

        $this->deleteLoanRepayments($loanDetail->id);
        return $loanDetail->delete();
    }

    public function getEmployeeLoanRepayment($loanId, $employeeId)
    {
        return LoanRepayment::where('loan_id', $loanId)->where('employee_id', $employeeId)->exists();
    }
    public function deleteLoanRepayments($loanId)
    {
        return LoanRepayment::where('loan_id', $loanId)->delete();
    }


    public function checkPendingLoan($employeeId)
    {
        return Loan::where('employee_id', $employeeId)
            ->where(function($query) {
                $query->where('status', LoanStatusEnum::pending->value)
                    ->orWhere(function($subQuery) {
                        $subQuery->where('status', LoanStatusEnum::approve->value)
                            ->whereHas('loanRepayment', function($repayQuery) {
                                $repayQuery->whereIn('status', [LoanRepaymentStatusEnum::active->value, LoanRepaymentStatusEnum::upcoming->value]);
                            });
                    });
            })
            ->exists();
    }


    public function checkSettledLoansInCurrentMonthAndYear($employeeId)
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        return Loan::where('employee_id', $employeeId)
            ->where('status', LoanStatusEnum::settled->value)
            ->whereHas('loanRepayment', function ($repayQuery) use ($currentYear, $currentMonth) {
                $repayQuery->whereYear('paid_date', $currentYear)
                    ->whereMonth('paid_date', $currentMonth)
                    ->where('status', LoanRepaymentStatusEnum::paid->value)
                    ->whereIn('id', function ($subQuery) {

                        $subQuery->select(DB::raw('MAX(id)'))
                            ->from('loan_repayments')
                            ->whereColumn('loan_id', 'loans.id');
                    });
            })
            ->exists();
    }
}

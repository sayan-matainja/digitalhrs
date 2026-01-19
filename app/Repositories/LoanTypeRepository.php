<?php

namespace App\Repositories;

use App\Models\LoanType;

class LoanTypeRepository
{

    public function getAllLoanTypes($filterParameters,$select=['*'],$with=[])
    {
        return LoanType::select($select)->withCount($with)
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->where('name', 'like', '%' . $filterParameters['type'] . '%');
            })
            ->get();
    }

    public function getAllActiveLoanTypes($select=['*'],$with=[])
    {
        return LoanType::select($select)->with($with)->where('is_active',1)->get();
    }

    public function getBranchLoanTypes($branchId,$select=['*'])
    {
        return LoanType::select($select)->where('branch_id',$branchId)->where('is_active',1)->get();
    }

    public function findLoanTypeById($id,$select=['*'],$with=[])
    {
        return LoanType::select($select)->with($with)->where('id',$id)->first();
    }

    public function create($validatedData)
    {
        return LoanType::create($validatedData)->fresh();
    }

    public function update($loanTypeDetail,$validatedData)
    {
        return $loanTypeDetail->update($validatedData);
    }

    public function delete($loanTypeDetail)
    {
        return $loanTypeDetail->delete();
    }

    public function toggleIsActiveStatus($loanTypeDetail)
    {
        return $loanTypeDetail->update([
            'is_active' => !$loanTypeDetail->is_active,
        ]);
    }
}

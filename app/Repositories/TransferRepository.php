<?php

namespace App\Repositories;

use App\Models\Transfer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransferRepository
{

    public function getAllTransferPaginated($filterParameters,$select=['*'],$with=[])
    {
        return Transfer::with($with)->select($select)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id'])
                    ->orWhere('old_branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('department_id', $filterParameters['department_id'])
                    ->orWhere('old_department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function($query) use ($filterParameters){
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->when(isset($filterParameters['transfer_date']), function($query) use ($filterParameters){
                $query->whereDate('transfer_date',date('Y-m-d',strtotime($filterParameters['transfer_date'])));
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Transfer::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        return Transfer::create($validatedData)->fresh();
    }

    public function update($transferDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id ?? null;

        return $transferDetail->update($validatedData);
    }


    public function delete($transferDetail)
    {
        return $transferDetail->delete();
    }




}

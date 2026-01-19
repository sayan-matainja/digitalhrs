<?php

namespace App\Repositories;

use App\Models\Promotion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PromotionRepository
{

    public function getAllPromotionPaginated($filterParameters,$select=['*'],$with=[])
    {

        return Promotion::with($with)->select($select)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function($query) use ($filterParameters){
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function($query) use ($filterParameters){
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->when(isset($filterParameters['promotion_date']), function($query) use ($filterParameters){
                $query->whereDate('promotion_date',date('Y-m-d',strtotime($filterParameters['promotion_date'])));
            })
            ->latest()
            ->paginate( getRecordPerPage());
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Promotion::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        return Promotion::create($validatedData)->fresh();
    }

    public function update($promotionDetail,$validatedData)
    {
        $validatedData['updated_by'] = auth()->user()->id ?? null;

        return $promotionDetail->update($validatedData);
    }


    public function delete($promotionDetail)
    {
        return $promotionDetail->delete();
    }




}

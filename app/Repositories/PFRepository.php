<?php

namespace App\Repositories;


use App\Models\PF;

class PFRepository
{

    public function find($id)
    {
        return PF::where('id',$id)
            ->firstOrFail();
    }

    public function getDetail($select=['*'])
    {
        return PF::select($select)->where('is_active',1)->first();
    }
    public function getPF($select=['*'])
    {
        return PF::select($select)->first();
    }


    public function store($validatedData)
    {
        return PF::create($validatedData)->fresh();
    }

    public function update($PFDetail, $validatedData)
    {
        return $PFDetail->update($validatedData);
    }
    public function getDetailForPF($startDate, $endDate)
    {
        return PF::where('is_active',1)->where('applicable_date', '<', $startDate)
            ->orWhere('applicable_date', '<', $endDate)->first();
    }

    public function getPFForTax($startDate)
    {
        return PF::where('is_active',1)->where('applicable_date', '<=', $startDate)->first();
    }
}

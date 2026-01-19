<?php

namespace App\Repositories;


use App\Models\SSF;

class SSFRepository
{

    public function find($id)
    {
        return SSF::where('id',$id)
            ->firstOrFail();
    }

    public function getDetail($select=['*'])
    {
        return SSF::select($select)->where('is_active',1)->first();
    }
    public function getSSF($select=['*'])
    {
        return SSF::select($select)->first();
    }


    public function store($validatedData)
    {
        return SSF::create($validatedData)->fresh();
    }

    public function update($ssfDetail, $validatedData)
    {
        return $ssfDetail->update($validatedData);
    }
    public function getDetailForSSF($startDate, $endDate)
    {
        return SSF::where('is_active',1)->where('applicable_date', '<', $startDate)
            ->orWhere('applicable_date', '<', $endDate)->first();
    }

    public function getSSFForTax($startDate)
    {
        return SSF::where('is_active',1)->where('applicable_date', '<=', $startDate)->first();
    }
}

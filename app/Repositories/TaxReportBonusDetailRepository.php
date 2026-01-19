<?php

namespace App\Repositories;


use App\Models\TaxReportBonusDetail;

class TaxReportBonusDetailRepository
{

    public function getAll($select=['*'],$with=[])
    {
        return TaxReportBonusDetail::select($select)->with($with)->get();
    }

    public function find($id, $select=['*'],$with=[])
    {
        return TaxReportBonusDetail::select($select)->with($with)->where('id',$id)->get();
    }


    public function create($validatedData)
    {
        return TaxReportBonusDetail::create($validatedData)->fresh();
    }

    public function update($bonusDetail,$validatedData)
    {
        return $bonusDetail->update($validatedData);
    }

    public function deleteByTaxReportId($taxReportId){
        return TaxReportBonusDetail::where('tax_report_id',$taxReportId)->delete();
    }
}

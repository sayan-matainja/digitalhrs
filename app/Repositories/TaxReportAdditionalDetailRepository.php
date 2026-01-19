<?php

namespace App\Repositories;



use App\Models\TaxReportAdditionalDetail;

class TaxReportAdditionalDetailRepository
{

    public function getAll($select=['*'],$with=[])
    {
        return TaxReportAdditionalDetail::select($select)->with($with)->get();
    }

    public function find($id, $select=['*'],$with=[])
    {
        return TaxReportAdditionalDetail::select($select)->with($with)->where('id',$id)->first();
    }

    public function create($validatedData)
    {
        return TaxReportAdditionalDetail::create($validatedData)->fresh();
    }

    public function update($additionalDetail,$validatedData)
    {
        return $additionalDetail->update($validatedData);
    }

    public function deleteByTaxReportId($taxReportId){
        return TaxReportAdditionalDetail::where('tax_report_id',$taxReportId)->delete();
    }

}

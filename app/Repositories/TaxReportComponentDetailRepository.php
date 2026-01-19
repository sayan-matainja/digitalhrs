<?php

namespace App\Repositories;



use App\Models\TaxReportComponentDetail;

class TaxReportComponentDetailRepository
{

    public function getAllComponentDetail($select=['*'],$with=[])
    {
        return TaxReportComponentDetail::select($select)->with($with)->get();
    }

    public function find($id, $select=['*'],$with=[])
    {
        return TaxReportComponentDetail::select($select)->with($with)->where('id',$id)->first();
    }


    public function create($validatedData)
    {
        return TaxReportComponentDetail::create($validatedData)->fresh();
    }

    public function update($ComponentDetail,$validatedData)
    {
        return $ComponentDetail->update($validatedData);
    }

    public function deleteByTaxReportId($taxReportId){
        return TaxReportComponentDetail::where('tax_report_id',$taxReportId)->delete();
    }

}

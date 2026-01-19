<?php

namespace App\Repositories;



use App\Models\TaxReportTdsDetail;

class TaxReportTdsDetailRepository
{

    public function getAllTaxReport($select=['*'],$with=[])
    {
        return TaxReportTdsDetail::select($select)->with($with)->get();
    }

    public function find($id, $select=['*'],$with=[])
    {
        return TaxReportTdsDetail::select($select)->with($with)->where('id',$id)->first();
    }

    public function findByReportMonth($taxReportId, $month)
    {
        return TaxReportTdsDetail::where('tax_report_id',$taxReportId)->where('month',$month)->first();
    }


    public function create($validatedData)
    {
        return TaxReportTdsDetail::create($validatedData)->fresh();
    }

    public function update($taxReportDetail,$validatedData)
    {
        return $taxReportDetail->update($validatedData);
    }

    public function deleteByTaxReportId($taxReportId){
        return TaxReportTdsDetail::where('tax_report_id',$taxReportId)->delete();
    }

}

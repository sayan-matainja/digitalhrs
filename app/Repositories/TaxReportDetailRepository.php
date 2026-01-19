<?php

namespace App\Repositories;




use App\Models\TaxReportDetail;

class TaxReportDetailRepository
{

    public function getAll($select=['*'],$with=[])
    {
        return TaxReportDetail::select($select)->with($with)->get();
    }

    public function find($id, $select=['*'],$with=[])
    {
        return TaxReportDetail::select($select)->with($with)->where('id',$id)->first();
    }

    public function findByMonth($taxReportId, $month)
    {
        return TaxReportDetail::where('tax_report_id',$taxReportId)->where('month',$month)->first();
    }


    public function create($validatedData)
    {
        return TaxReportDetail::create($validatedData)->fresh();
    }

    public function update($taxReportDetail,$validatedData)
    {
        return $taxReportDetail->update($validatedData);
    }

    public function deleteByTaxReportId($taxReportId){
        return TaxReportDetail::where('tax_report_id',$taxReportId)->delete();
    }


}

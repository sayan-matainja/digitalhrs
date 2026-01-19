<?php

namespace App\Repositories;


use App\Helpers\AppHelper;
use App\Models\FiscalYear;
use Exception;

class FiscalYearRepository
{

    public function getAllFiscalYears($select=['*'])
    {
        return FiscalYear::select($select)->get();
    }

    public function getActiveFiscalYear($select=['*'])
    {
        return FiscalYear::select($select)->where('is_running',1)->first();
    }

    public function find($id,$select=['*'])
    {
        return FiscalYear::select($select)->where('id',$id)->first();
    }
    public function findLatest($select=['*'])
    {
        return FiscalYear::select($select)->first();
    }

    public function create($validatedData)
    {
        return FiscalYear::create($validatedData)->fresh();
    }

    public function update($fiscalYearDetail,$validatedData)
    {
        return $fiscalYearDetail->update($validatedData);
    }

    public function delete($fiscalYearDetail)
    {
        return $fiscalYearDetail->delete();
    }

    public function fiscalYearOverlaps($startDate, $endDate, $id=0)
    {
        return FiscalYear::where('id', '!=', $id)
            ->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        })->exists();
    }

    /**
     * @throws Exception
     */
    public function toggleIsRunning($fiscalYearDetail)
    {
        $firstDay = $fiscalYearDetail->start_date;
        $lastDay = $fiscalYearDetail->end_date;
        $startYear = AppHelper::getYearValue($firstDay);
        $endYear = AppHelper::getYearValue($lastDay);
        $currentYear = AppHelper::getRunningYear();

        if($fiscalYearDetail->is_running == 0 && (($startYear != $currentYear) || ($endYear != $currentYear))){

            throw new Exception(__('index.old_fiscal_year_status_warning'),400);
        }else{
            FiscalYear::where('id','!=',$fiscalYearDetail->id)->update(['is_running' => 0]);
        }
        if(($fiscalYearDetail->is_running == 1) && (($startYear == $currentYear) || ($endYear == $currentYear))){
            throw new Exception(__('index.fiscal_year_status_warning'),400);
        }

        return $fiscalYearDetail->update([
            'is_running' => !$fiscalYearDetail->is_running
        ]);
    }
}

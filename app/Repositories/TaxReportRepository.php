<?php

namespace App\Repositories;


use App\Models\TaxReport;

class TaxReportRepository
{
    const STATUS_VERIFIED = 'verified';

    public function getAll()
    {

        return TaxReport::select('tax_reports.id', 'fiscal_years.year', 'users.name', 'tax_reports.total_payable_tds')
            ->leftJoin('users', 'tax_reports.employee_id', 'users.id')
            ->leftJoin('fiscal_years', 'tax_reports.fiscal_year_id', 'fiscal_years.id')
            ->where('users.status', self::STATUS_VERIFIED)
            ->get();
    }

    public function getTaxReportByEmployee($employeeId, $fiscalYearId, $select = ['*'], $with = [])
    {
        return TaxReport::select($select)->with($with)->where('employee_id', $employeeId)->where('fiscal_year_id', $fiscalYearId)->get();
    }

    public function find($id, $select = ['*'], $with = [])
    {
        return TaxReport::select($select)->with($with)->where('id', $id)->first();
    }

    public function findByEmployee($employeeId, $fiscalYearId)
    {

        return TaxReport::select('tax_reports.id', 'fiscal_years.year', 'users.name', 'tax_reports.total_payable_tds')
            ->leftJoin('users', 'tax_reports.employee_id', 'users.id')
            ->leftJoin('fiscal_years', 'tax_reports.fiscal_year_id', 'fiscal_years.id')
            ->where('tax_reports.employee_id', $employeeId)
            ->where('tax_reports.fiscal_year_id', $fiscalYearId)
            ->where('users.status', self::STATUS_VERIFIED)
            ->first();
    }

    public function create($validatedData)
    {
        return TaxReport::create($validatedData)->fresh();
    }

    public function update($taxReportDetail, $validatedData)
    {
        return $taxReportDetail->update($validatedData);
    }

    public function delete($taxReportDetail)
    {
        $taxReportDetail->additionalDetail()->delete();
        $taxReportDetail->bonusDetail()->delete();
        $taxReportDetail->tdsDetail()->delete();
        $taxReportDetail->componentDetail()->delete();
        $taxReportDetail->reportDetail()->delete();
        return $taxReportDetail->delete();
    }

}

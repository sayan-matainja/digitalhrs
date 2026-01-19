<?php

namespace App\Services\TaxReport;

use App\Repositories\TaxReportDetailRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class TaxReportDetailService
{
    public function __construct(
        protected TaxReportDetailRepository $reportDetailRepository
    ){}
    /**
     * @throws Exception
     */
    public function getAllReportDetails($select= ['*'],$with=[])
    {
        return $this->reportDetailRepository->getAll($select, $with);
    }

    /**
     * @throws Exception
     */
    public function findReportDetailById($id,$select=['*'],$with=[])
    {
        return  $this->reportDetailRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function findReportDetailByMonth($taxReportId, $month)
    {
        return  $this->reportDetailRepository->findByMonth($taxReportId, $month);

    }


    /**
     * @throws Exception
     */
    public function store($taxReportId, $validatedData)
    {
        foreach ($validatedData as $month => $value) {
            $dataToStore = [
                'tax_report_id' => $taxReportId,
                'month' => $month,
                'salary'=>$value['salary'],
                'basic_salary'=>$value['basic_salary'],
                'fixed_allowance'=>$value['fixed_allowance'],
                'ssf_contribution'=>$value['ssf_contribution'],
                'ssf_deduction'=>$value['ssf_deduction'],
                'pf_contribution'=>$value['pf_contribution'],
                'pf_deduction'=>$value['pf_deduction'],
            ];

           $this->reportDetailRepository->create($dataToStore);
        }

    }
    /**
     * @throws Exception
     */
    public function updateReportDetail($taxReportId, $validatedData)
    {
        foreach ($validatedData as $month => $amount) {

            $tdsDetail = $this->findReportDetailById($taxReportId, $month);

            $this->reportDetailRepository->update($tdsDetail, ['amount'=>$amount]);

        }

    }

    public function deleteReportDetail($taxReportId)
    {
        $this->reportDetailRepository->deleteByTaxReportId($taxReportId);
    }



}

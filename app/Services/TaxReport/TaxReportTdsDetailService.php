<?php

namespace App\Services\TaxReport;

use App\Repositories\TaxReportTdsDetailRepository;
use Exception;

class TaxReportTdsDetailService
{
    public function __construct(
        protected TaxReportTdsDetailRepository $tdsDetailRepository
    ){}
    /**
     * @throws Exception
     */
    public function getAllTdsDetails($select= ['*'],$with=[])
    {
        return $this->tdsDetailRepository->getAllTaxReport($select, $with);
    }

    /**
     * @throws Exception
     */
    public function findTdsDetailById($id,$select=['*'],$with=[])
    {
        return  $this->tdsDetailRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function findTdsDetailByReportMonth($taxReportId, $month)
    {
        return  $this->tdsDetailRepository->findByReportMonth($taxReportId, $month);

    }


    /**
     * @throws Exception
     */
    public function store($taxReportId, $validatedData)
    {
        foreach ($validatedData as $month => $amount) {
            $dataToStore = [
                'tax_report_id' => $taxReportId,
                'month' => $month,
                'amount' => $amount,
                'is_paid' => false
            ];

            $this->tdsDetailRepository->create($dataToStore);
        }

    }
    /**
     * @throws Exception
     */
    public function updateTdsDetail($taxReportId, $validatedData)
    {
        foreach ($validatedData as $month => $amount) {

            $tdsDetail = $this->findTdsDetailByReportMonth($taxReportId, $month);

            $this->tdsDetailRepository->update($tdsDetail, ['amount'=>$amount]);

        }

    }

    public function deleteTdsDetail($taxReportId)
    {
        $this->tdsDetailRepository->deleteByTaxReportId($taxReportId);
    }

}

<?php

namespace App\Services\TaxReport;

use App\Repositories\TaxReportAdditionalDetailRepository;
use Exception;

class TaxReportAdditionalDetailService
{
    public function __construct(
        protected TaxReportAdditionalDetailRepository $additionalDetailRepository
    ){}
    /**
     * @throws Exception
     */
    public function getAllAdditionalDetails($select= ['*'],$with=[])
    {
        return $this->additionalDetailRepository->getAll($select, $with);
    }

    /**
     * @throws Exception
     */
    public function findAdditionalDetailById($id,$select=['*'],$with=[])
    {
        return  $this->additionalDetailRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function store($taxReportId, $validatedData)
    {

        foreach($validatedData as $month=>$data) {
            foreach($data as $d) {

                $dataToStore = [
                    'tax_report_id'=>$taxReportId,
                    'month' => $month,
                    'salary_component_id' => $d['salary_component_id'],
                    'amount' => $d['amount'],
                ];

                $this->additionalDetailRepository->create($dataToStore);

            }
        }
    }
    /**
     * @throws Exception
     */
    public function updateAdditionalDetail($validatedData)
    {

        foreach($validatedData as $key => $data) {

          $additionalData = $this->findAdditionalDetailById($key);

          $this->additionalDetailRepository->update($additionalData, ['amount'=>$data]);

        }

        return true;

    }


    public function deleteadditionalDetail($taxReportId)
    {
        $this->additionalDetailRepository->deleteByTaxReportId($taxReportId);
    }
}

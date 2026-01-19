<?php

namespace App\Services\TaxReport;

use App\Repositories\TaxReportComponentDetailRepository;
use Exception;

class TaxReportComponentDetailService
{
    public function __construct(
        protected TaxReportComponentDetailRepository $componentDetailRepository
    ){}
    /**
     * @throws Exception
     */
    public function getAllComponentDetails($select= ['*'],$with=[])
    {
        return $this->componentDetailRepository->getAllComponentDetail($select, $with);
    }

    /**
     * @throws Exception
     */
    public function findComponentDetailById($id,$select=['*'],$with=[])
    {
        return  $this->componentDetailRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function store($taxReportId, $validatedData)
    {

        foreach($validatedData as $month=>$data) {
            foreach( $data as $d) {
                $dataToStore = [
                    'tax_report_id'=>$taxReportId,
                    'month' => $month,
                    'salary_component_id' => $d['salary_component_id'],
                    'type' => $d['type'],
                    'amount' => $d['amount'],
                ];

                $this->componentDetailRepository->create($dataToStore);

            }
        }
        return true;

    }
    /**
     * @throws Exception
     */
    public function updateComponentDetail($id, $validatedData)
    {

            $componentDetail = $this->findComponentDetailById($id);

            return $this->componentDetailRepository->update($componentDetail, $validatedData);

    }

    public function deleteComponentDetail($taxReportId)
    {
        $this->componentDetailRepository->deleteByTaxReportId($taxReportId);
    }

}

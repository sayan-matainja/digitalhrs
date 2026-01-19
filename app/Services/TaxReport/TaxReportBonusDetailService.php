<?php

namespace App\Services\TaxReport;

use App\Repositories\TaxReportBonusDetailRepository;
use Exception;

class TaxReportBonusDetailService
{
    public function __construct(
        protected TaxReportBonusDetailRepository $bonusDetailRepository
    ){}
    /**
     * @throws Exception
     */
    public function getAllBonusDetails($select= ['*'],$with=[])
    {
        return $this->bonusDetailRepository->getAll($select, $with);
    }

    /**
     * @throws Exception
     */
    public function findBonusDetailById($id,$select=['*'],$with=[])
    {
        return  $this->bonusDetailRepository->find($id,$select,$with);

    }

    /**
     * @throws Exception
     */
    public function store($taxReportId, $validatedData)
    {
        foreach ($validatedData as $month => $bonus) {
            $dataToStore = [
                'tax_report_id' => $taxReportId,
                'month' => $month,
                'bonus_id' => $bonus['id'],
                'tax' => $bonus['tax'],
                'amount' => $bonus['amount'],
            ];

            $this->bonusDetailRepository->create($dataToStore);
        }

    }
    /**
     * @throws Exception
     */
    public function updateBonusDetail($id, $validatedData)
    {

            $bonusDetail = $this->findBonusDetailById($id);

            return $this->bonusDetailRepository->update($bonusDetail, $validatedData);

    }


    public function deleteBonusDetail($taxReportId)
    {
        $this->bonusDetailRepository->deleteByTaxReportId($taxReportId);
    }
}

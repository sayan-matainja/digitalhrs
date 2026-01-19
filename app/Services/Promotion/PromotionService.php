<?php

namespace App\Services\Promotion;

use App\Helpers\AppHelper;
use App\Repositories\PromotionRepository;
use Exception;

class PromotionService
{
    public function __construct(
        protected PromotionRepository $promotionRepository
    )
    {
    }

    public function getAllPromotionPaginated($filterParameters,$select = ['*'], $with = [])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['promotion_date'] = isset($filterParameters['promotion_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['promotion_date']): null;

        }
        return $this->promotionRepository->getAllPromotionPaginated($filterParameters,$select, $with);
    }




    /**
     * @throws Exception
     */
    public function findPromotionById($id, $select = ['*'], $with = [])
    {
        return $this->promotionRepository->find($id, $select, $with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function savePromotionDetail($validatedData)
    {
        $validatedData['created_by'] = auth()->user()->id ?? null;

        return $this->promotionRepository->store($validatedData);

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updatePromotionDetail($id, $validatedData)
    {

        $promotionDetail = $this->findPromotionById($id);
        $this->promotionRepository->update($promotionDetail, $validatedData);


        return $promotionDetail;
    }

    /**
     * @throws Exception
     */
    public function deletePromotion($id)
    {
        $promotionDetail = $this->findPromotionById($id);
        return $this->promotionRepository->delete($promotionDetail);
    }


    /**
     * @param $promotionId
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateStatus($promotionId, $validatedData){
        $promotionDetail = $this->findPromotionById($promotionId);

        $this->promotionRepository->update($promotionDetail, $validatedData);

        return $promotionDetail;

    }
}

<?php

namespace App\Services\AwardManagement;

use App\Helpers\AppHelper;
use App\Repositories\AwardRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class AwardService
{
    public function __construct(
        protected AwardRepository $awardRepository
    ){}

    public function getAllAwardPaginated($filterParameters,$select= ['*'],$with=[])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['awarded_date'] = isset($filterParameters['awarded_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['awarded_date']): null;

        }
        return $this->awardRepository->getAllAwardsPaginated($filterParameters,$select,$with);
    }

    public function getEmployeeAward($employeeId,$perPage, $select= ['*'],$with=[], $userProfile=0)
    {
        return $this->awardRepository->getEmployeeAwardsPaginated($employeeId,$perPage,$select,$with,$userProfile);
    }

    public function getRecentEmployeeAward( $select,$with,$employeeId = 0)
    {
        return $this->awardRepository->getRecentAward($select,$with,$employeeId);
    }

    /**
     * @throws Exception
     */
    public function findAwardById($id, $select=['*'], $with=[])
    {
        return $this->awardRepository->findAwardById($id,$select,$with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveAwardDetail($validatedData)
    {
        return $this->awardRepository->store($validatedData);

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateAwardDetail($id, $validatedData)
    {
        $awardDetail = $this->findAwardById($id);
        return $this->awardRepository->update($awardDetail, $validatedData);

    }

    /**
     * @throws Exception
     */
    public function deleteAward($id)
    {
        $awardDetail = $this->findAwardById($id);
        return $this->awardRepository->delete($awardDetail);
    }

    public function checkAwardType($typeId)
    {
        return $this->awardRepository->checkType($typeId);
    }




}

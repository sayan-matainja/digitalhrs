<?php

namespace App\Services\Resignation;

use App\Enum\ResignationStatusEnum;
use App\Helpers\AppHelper;
use App\Repositories\ResignationRepository;
use Exception;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;

class ResignationService
{
    public function __construct(
        protected ResignationRepository $resignationRepository
    ){}

    public function getAllResignationPaginated($filterParameters,$select= ['*'],$with=[])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['resignation_date'] = isset($filterParameters['resignation_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['resignation_date']): null;

        }
        return $this->resignationRepository->getAllResignationPaginated($filterParameters,$select,$with);
    }

    /**
     * @throws Exception
     */
    public function findResignationById($id, $select=['*'], $with=[])
    {
        return $this->resignationRepository->find($id,$select,$with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveResignationDetail($validatedData): mixed
    {
        $resignationData = $this->findResignationByEmployeeId($validatedData['employee_id']);

        if(isset($resignationData) && $resignationData->status != ResignationStatusEnum::cancelled->value){
            throw new Exception(__('index.resignation_exist_error',[
            'status' => ucfirst($resignationData->status)]),404);
        }


        return $this->resignationRepository->store($validatedData);

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateResignationDetail($id, $validatedData): mixed
    {
        $resignationDetail = $this->findResignationById($id);

        $this->resignationRepository->update($resignationDetail, $validatedData);

        return $resignationDetail;

    }

    /**
     * @throws Exception
     */
    public function deleteResignation($id)
    {
        $resignationDetail = $this->findResignationById($id);
        return $this->resignationRepository->delete($resignationDetail);
    }

    /**
     * @param $resignationId
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateStatus($resignationId, $validatedData){
        $resignationDetail = $this->findResignationById($resignationId);

       $this->resignationRepository->update($resignationDetail, $validatedData);

        return $resignationDetail;

    }

    /**
     * @throws Exception
     */
    public function findResignationByEmployeeId($employeeId,$select=['*'])
    {
        return $this->resignationRepository->findByEmployeeId($employeeId,$select);
    }




}

<?php

namespace App\Services\Warning;

use App\Helpers\AppHelper;
use App\Repositories\WarningRepository;
use Exception;

class WarningService
{
    public function __construct(
        protected WarningRepository $warningRepository
    )
    {
    }

    public function getAllWarningPaginated($filterParameters,$select = ['*'], $with = [])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['warning_date'] = isset($filterParameters['warning_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['warning_date']): null;

        }
        return $this->warningRepository->getAllWarningPaginated($filterParameters,$select, $with);
    }

    public function getApiWarning($perPage, $select = ['*'], $with = [])
    {
        return $this->warningRepository->getEmployeeWarningPaginated($perPage, $select, $with);
    }



    /**
     * @throws Exception
     */
    public function findWarningById($id, $select = ['*'], $with = [])
    {
        return $this->warningRepository->find($id, $select, $with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveWarningDetail($validatedData)
    {
        $relationData = $this->getWarningRelationData($validatedData);
        $validatedData['created_by'] = auth()->user()->id ?? null;

        $warningDetail = $this->warningRepository->store($validatedData);

        if ($warningDetail) {
            $this->warningRepository->saveEmployee($warningDetail, $relationData['employee']);
            $this->warningRepository->saveDepartment($warningDetail, $relationData['department']);
        }
        return $warningDetail;

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateWarningDetail($id, $validatedData)
    {

        $relationData = $this->getWarningRelationData($validatedData);
        $warningDetail = $this->findWarningById($id);
        $status = $this->warningRepository->update($warningDetail, $validatedData);

        if($status){
            $this->warningRepository->updateEmployee($warningDetail, $relationData['employee']);
            $this->warningRepository->updateDepartment($warningDetail, $relationData['department']);
        }

        return $warningDetail;
    }

    /**
     * @throws Exception
     */
    public function deleteWarning($id)
    {
        $warningDetail = $this->findWarningById($id);
        return $this->warningRepository->delete($warningDetail);
    }



    private function getWarningRelationData($validatedData): array
    {
        return [
            'employee' => array_map(fn($id) => ['employee_id' => $id], $validatedData['employee_id']),
            'department' => array_map(fn($id) => ['department_id' => $id], $validatedData['department_id']),
        ];
    }

    /**
     * @throws Exception
     */
    public function saveWarningResponse($validatedData, $warningId)
    {
        $warningDetail = $this->findWarningById($warningId);

        if ($warningDetail) {
            $this->warningRepository->saveResponse($warningDetail, $validatedData);
        }
        return $warningDetail;

    }


}

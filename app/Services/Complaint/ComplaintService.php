<?php

namespace App\Services\Complaint;

use App\Helpers\AppHelper;
use App\Repositories\ComplaintRepository;
use Exception;

class ComplaintService
{
    public function __construct(
        protected ComplaintRepository $complaintRepository
    )
    {
    }

    public function getAllComplaintPaginated($filterParameters,$select = ['*'], $with = [])
    {
        if(AppHelper::ifDateInBsEnabled()){
            $filterParameters['complaint_date'] = isset($filterParameters['complaint_date']) ?
                AppHelper::dateInYmdFormatNepToEng($filterParameters['complaint_date']): null;

        }
        return $this->complaintRepository->getAllComplaintPaginated($filterParameters,$select, $with);
    }

    public function getApiComplaint($perPage, $select = ['*'], $with = [])
    {
        return $this->complaintRepository->getEmployeeComplaintPaginated($perPage, $select, $with);
    }



    /**
     * @throws Exception
     */
    public function findComplaintById($id, $select = ['*'], $with = [])
    {
        return $this->complaintRepository->find($id, $select, $with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveComplaintDetail($validatedData)
    {
        $relationData = $this->getComplaintRelationData($validatedData);
        $validatedData['created_by'] = auth()->user()->id ?? null;
        $validatedData['complaint_date'] = now();

        $complaintDetail = $this->complaintRepository->store($validatedData);

        if ($complaintDetail) {
            $this->complaintRepository->saveEmployee($complaintDetail, $relationData['employee']);
            $this->complaintRepository->saveDepartment($complaintDetail, $relationData['department']);
        }
        return $complaintDetail;

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateComplaintDetail($id, $validatedData)
    {

        $relationData = $this->getComplaintRelationData($validatedData);
        $complaintDetail = $this->findComplaintById($id);
        $status = $this->complaintRepository->update($complaintDetail, $validatedData);

        if($status){
            $this->complaintRepository->updateEmployee($complaintDetail, $relationData['employee']);
            $this->complaintRepository->updateDepartment($complaintDetail, $relationData['department']);
        }

        return $complaintDetail;
    }

    /**
     * @throws Exception
     */
    public function deleteComplaint($id)
    {
        $complaintDetail = $this->findComplaintById($id);
        return $this->complaintRepository->delete($complaintDetail);
    }



    private function getComplaintRelationData($validatedData): array
    {
        return [
            'employee' => array_map(fn($id) => ['employee_id' => $id], $validatedData['employee_id']),
            'department' => array_map(fn($id) => ['department_id' => $id], $validatedData['department_id']),
        ];
    }

    /**
     * @throws Exception
     */
    public function saveComplaintResponse($validatedData, $complaintId)
    {
        $complaintDetail = $this->findComplaintById($complaintId);

        if ($complaintDetail) {
            $this->complaintRepository->saveResponse($complaintDetail, $validatedData);
        }
        return $complaintDetail;

    }


}

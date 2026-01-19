<?php

namespace App\Services\Leave;

use App\Enum\LeaveApproverEnum;
use App\Models\LeaveApprovalDepartment;
use App\Repositories\DepartmentRepository;
use App\Repositories\LeaveApprovalRepository;
use Exception;

class LeaveApprovalService
{
    public function __construct(
        protected LeaveApprovalRepository $approvalRepository, protected  DepartmentRepository $departmentRepository
    ){}

    public function getAllLeaveApprovalPaginated($filterParameters,$select= ['*'], $with=[])
    {

        return $this->approvalRepository->getAll($filterParameters,$select,$with);
    }

    /**
     * @throws Exception
     */
    public function findLeaveApprovalById($id, $select=['*'], $with=[])
    {
        return $this->approvalRepository->find($id,$select,$with);
    }

    /**
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function saveLeaveApprovalDetail($validatedData)
    {

        $departmentIds = $validatedData['department_id'];
        $leaveTypeId = $validatedData['leave_type_id'];

        $check = $this->approvalRepository->checkLeaveAndDepartment($leaveTypeId, $departmentIds);

        if($check){
            throw new Exception('Leave Approval with selected leave type and departments already exists.', 400);
        }

        $headCheck = $this->departmentRepository->checkSpecificDepartmentHead($departmentIds, $validatedData['user_id']);

        if($headCheck){
            throw new Exception('The department head of selected department is same as the selected specific personnel.', 400);
        }

        $approvalData = $this->getLeaveApprovalData($validatedData);



        $validatedData['max_days_limit'] = $validatedData['max_days_limit'] ?? 0;
        $leaveApprovalDetail = $this->approvalRepository->store($validatedData);

        $this->approvalRepository->saveApprovalDepartment($leaveApprovalDetail,$approvalData['department']);
        $this->approvalRepository->saveApprovalProcess($leaveApprovalDetail,$approvalData['process']);

        return $leaveApprovalDetail;

    }

    /**
     * @param $id
     * @param $validatedData
     * @return mixed
     * @throws Exception
     */
    public function updateLeaveApprovalDetail($id, $validatedData)
    {

        $departmentIds = $validatedData['department_id'];
        $leaveTypeId = $validatedData['leave_type_id'];

        $check = $this->approvalRepository->checkExistingLeaveAndDepartment($id, $leaveTypeId, $departmentIds);

        if($check){
            throw new Exception('Leave Approval with selected leave type and departments already exists.', 400);
        }

        $headCheck = $this->departmentRepository->checkSpecificDepartmentHead($departmentIds, $validatedData['user_id']);

        if($headCheck){
            throw new Exception('The department head of selected department is same as the selected specific personnel.', 400);
        }

        $leaveApprovalDetail = $this->findLeaveApprovalById($id);
        $approvalData = $this->getLeaveApprovalData($validatedData);

        $this->approvalRepository->update($leaveApprovalDetail, $validatedData);

        $this->approvalRepository->updateApprovalDepartment($leaveApprovalDetail,$approvalData['department']);
        $this->approvalRepository->updateApprovalProcess($leaveApprovalDetail,$approvalData['process']);
        return $approvalData;

    }

    /**
     * @throws Exception
     */
    public function deleteLeaveApproval($id)
    {
        $leaveApprovalDetail = $this->findLeaveApprovalById($id);
        return $this->approvalRepository->delete($leaveApprovalDetail);
    }

    /**
     * @throws Exception
     */
    private function getLeaveApprovalData($validatedData): array
    {
        $departmentArray = [];
        $processArray = [];

        foreach ($validatedData['department_id'] as $department) {
            $departmentArray[] = ['department_id' => $department];
        }

        foreach ($validatedData['approver'] as $index => $approverValue) {
            $processArray[$index]['approver'] = $approverValue;

            if ($approverValue === LeaveApproverEnum::specific_personnel->value) {

                $processArray[$index]['role_id'] = $validatedData['role_id'][$index] ?? null;
                $processArray[$index]['user_id'] = $validatedData['user_id'][$index] ?? null;
            } else {
                $processArray[$index]['role_id'] = null;
                $processArray[$index]['user_id'] = null;
            }
        }

        return [
            'department' => $departmentArray,
            'process' => $processArray,
        ];
    }

    /**
     * @throws Exception
     */
    public function changeStatus($leaveApprovalId)
    {
        $leaveApprovalDetail = $this->findLeaveApprovalById($leaveApprovalId);
        return $this->approvalRepository->toggleStatus($leaveApprovalDetail);
    }



}

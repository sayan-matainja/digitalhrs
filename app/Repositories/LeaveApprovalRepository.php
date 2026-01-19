<?php

namespace App\Repositories;


use App\Helpers\AppHelper;
use App\Models\LeaveApproval;
use App\Models\LeaveApprovalDepartment;


class LeaveApprovalRepository
{

    public function getAll($filterParameters,$select=['*'], $with=[])
    {
        return LeaveApproval::select($select)
            ->with($with)
            ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->whereHas('approvalDepartment',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('department_id', $filterParameters['department_id']);
                });
            })
            ->when(isset($filterParameters['leave_type_id']), function($query) use ($filterParameters){
                $query->where('leave_type_id', $filterParameters['leave_type_id']);
            })
            ->paginate( getRecordPerPage());
    }


    public function find($id,$select=['*'], $with)
    {
        return LeaveApproval::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }


    public function store($validatedData)
    {
        return LeaveApproval::create($validatedData)->fresh();
    }

    public function update($leaveApprovalDetail,$validatedData)
    {
        return $leaveApprovalDetail->update($validatedData);
    }

    public function delete($leaveApprovalDetail)
    {
        $leaveApprovalDetail->approvalDepartment()->delete();
        $leaveApprovalDetail->approvalRole()->delete();
        $leaveApprovalDetail->notificationReceiver()->delete();
        $leaveApprovalDetail->approvalProcess()->delete();
        return $leaveApprovalDetail->delete();
    }

    public function saveApprovalDepartment(LeaveApproval $leaveApprovalDetail,$departmentArray)
    {
        return $leaveApprovalDetail->approvalDepartment()->createMany($departmentArray);
    }

    public function updateApprovalDepartment(LeaveApproval $leaveApprovalDetail,$departmentArray)
    {
        $leaveApprovalDetail->approvalDepartment()->delete();
        return $leaveApprovalDetail->approvalDepartment()->createMany($departmentArray);
    }

    public function saveApprovalRole(LeaveApproval $leaveApprovalDetail,$roleArray)
    {
        return $leaveApprovalDetail->approvalRole()->createMany($roleArray);
    }

    public function updateApprovalRole(LeaveApproval $leaveApprovalDetail,$roleArray)
    {
        $leaveApprovalDetail->approvalRole()->delete();
        return $leaveApprovalDetail->approvalRole()->createMany($roleArray);
    }

    public function saveNotificationReceiver(LeaveApproval $leaveApprovalDetail,$receiverArray)
    {
        return $leaveApprovalDetail->notificationReceiver()->createMany($receiverArray);
    }

    public function updateNotificationReceiver(LeaveApproval $leaveApprovalDetail,$receiverArray)
    {
        $leaveApprovalDetail->notificationReceiver()->delete();
        return $leaveApprovalDetail->notificationReceiver()->createMany($receiverArray);
    }

    public function saveApprovalProcess(LeaveApproval $leaveApprovalDetail,$processArray)
    {
        return $leaveApprovalDetail->approvalProcess()->createMany($processArray);
    }

    public function updateApprovalProcess(LeaveApproval $leaveApprovalDetail,$processArray)
    {
        $leaveApprovalDetail->approvalProcess()->delete();
        return $leaveApprovalDetail->approvalProcess()->createMany($processArray);
    }


    public function toggleStatus($leaveApprovalDetail)
    {
        return $leaveApprovalDetail->update([
            'status' => !$leaveApprovalDetail->status
        ]);
    }

    public function checkLeaveAndDepartment($leaveTypeId,$departmentIds)
    {

        $leaveApproval = LeaveApproval::where('leave_type_id', $leaveTypeId)->first();

        if (!$leaveApproval) {
            return false;
        }

        return LeaveApprovalDepartment::where('leave_approval_id', $leaveApproval->id)
            ->whereIn('department_id', $departmentIds)
            ->exists();
    }

    public function checkExistingLeaveAndDepartment($id, $leaveTypeId,$departmentIds)
    {

        $leaveApproval = LeaveApproval::where('leave_type_id', $leaveTypeId)->where('id','!=',$id)->first();

        if (!$leaveApproval) {
            return false;
        }

        return LeaveApprovalDepartment::where('leave_approval_id', $leaveApproval->id)
            ->whereIn('department_id', $departmentIds)
            ->exists();
    }


}

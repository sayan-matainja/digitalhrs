<?php

namespace App\Repositories;


use App\Models\LeaveRequestApproval;

class LeaveRequestApprovalRepository
{

    public function getAllLeaveRequestApproval($select=['*'],$with=[])
    {
        return LeaveRequestApproval::select($select)->withCount($with)->get();
    }

    public function getAllActiveLeaveRequestApproval($select=['*'])
    {
        return LeaveRequestApproval::select($select)->where('status',1)->get();
    }

    public function findByLeaveId($leaveRequestId,$with=[])
    {
        return LeaveRequestApproval::with($with)
            ->where('leave_request_id',$leaveRequestId)
            ->get();
    }

    public function create($validatedData)
    {
        return LeaveRequestApproval::create($validatedData)->fresh();
    }

    public function update($leaverequestApprovalDetail,$validatedData)
    {
        return $leaverequestApprovalDetail->update($validatedData);
    }


}

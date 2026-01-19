<?php

namespace App\Resources\Leave;

use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'leave_type_id' => $this->leave_type_id,
            'leave_type_name' => ucwords($this->leave_type_name ?? 'Time Leave'),
            'leave_type_slug ' => $this->leave_type_slug,
            'leave_type_status' => $this->leave_type_status==1,
            'early_exit' => $this->early_exit==1,
            'total_leave_allocated' => $this->total_leave_allocated,
            'leave_taken' => $this->leave_taken
        ];
    }
}













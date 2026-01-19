<?php

namespace App\Resources\Leave;


use App\Helpers\AppHelper;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeLeaveDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'leave_id' => $this->leave_id ?? '',
            'user_id' => $this->user_id ?? '',
            'title' => $this->leave_title,
            'user_name' => ucfirst($this->name),
            'user_avatar' => asset(User::AVATAR_UPLOAD_PATH.$this->avatar),
            'department' => ucfirst($this->department),
            'post' => ucfirst($this->post),
            'leave_days' => ($this->no_of_days),
            'leave_from' => AppHelper::convertLeaveDateFormat($this->leave_from),
            'leave_to' => isset($this->leave_to) ? AppHelper::convertLeaveDateFormat($this->leave_to) : '',
            'leave_from_ad' => date('Y-m-d', strtotime($this->leave_from)),
            'leave_to_ad' =>  $this->leave_to ? date('Y-m-d', strtotime($this->leave_to)) :  '',
            'leave_status' => ucfirst($this->leave_status),
        ];
    }
}















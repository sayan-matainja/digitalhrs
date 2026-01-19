<?php

namespace App\Resources\award;

use App\Helpers\AppHelper;
use App\Helpers\PMHelper;
use App\Models\Award;
use App\Models\Project;
use App\Models\User;
use App\Resources\AssignedMember\AssignedMemberCollection;
use App\Resources\Attachment\AttachmentCollection;
use App\Resources\Project\ProjectTaskCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class AwardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'award_name' => $this->type?->title,
            'employee_name' => $this->employee?->name,
            'employee_image' => $this->employee->avatar ?  asset(User::AVATAR_UPLOAD_PATH.$this->employee->avatar) : asset('assets/images/img.png'),
            'gift_item' => $this->gift_item,
            'awarded_date' => AppHelper::formatDateForView($this->awarded_date),
            'awarded_by' => $this->awarded_by,
            'award_description' => $this->award_description ?? '',
            'gift_description' => $this->gift_description ?? '',
            'reward_code' => $this->reward_code,
            'award_image' => $this->attachment ? asset(Award::UPLOAD_PATH.$this->attachment) : '',
        ];

    }
}













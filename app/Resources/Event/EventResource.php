<?php

namespace App\Resources\Event;

use App\Helpers\AppHelper;
use App\Models\Event;
use App\Models\User;
use App\Resources\User\TeamSheetResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * @throws \Exception
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id ?? '',
            'title' => ucfirst($this->title),
            'description' => removeHtmlTags($this->description),
            'host' => ucfirst($this->host) ?? AppHelper::getAuthUserCompanyName(),
            'location' => ucfirst($this->location),
            'start_date' => AppHelper::formatDateForView($this->start_date),
            'end_date' => isset($this->end_date) ? AppHelper::formatDateForView($this->end_date) : '',
            'start_date_ad' => $this->start_date,
            'end_date_ad' => $this->end_date ?? '',
            'start_time' => AppHelper::convertLeaveTimeFormat($this->start_time),
            'end_time' => AppHelper::convertLeaveTimeFormat($this->end_time),
            'image' => $this->attachment ? asset(Event::UPLOAD_PATH.$this->attachment) : '',
            'created_by' =>$this->createdBy?->name,
            'creator' => new TeamSheetResource($this->createdBy ?? new User()),
            'event_users' => new EventUserCollection($this->eventUser),
            'event_departments' => new EventDepartmentCollection($this->eventDepartment),
        ];
    }
}


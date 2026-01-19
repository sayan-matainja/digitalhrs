<?php

namespace App\Resources\TeamMeeting;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use App\Models\TeamMeeting;
use App\Models\User;
use App\Resources\User\TeamSheetResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMeetingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id ?? '',
            'title' => ucfirst($this->title),
            'agenda' => removeHtmlTags($this->description),
            'venue' => ucfirst($this->venue) ?? 'office',
            'meeting_date' => AppHelper::formatDateForView($this->meeting_date,false),
            'meeting_date_ad' => $this->meeting_date,
            'meeting_date_nepali' =>  AppHelper::formatDateForView($this->meeting_date,true),
            'meeting_start_time' => AttendanceHelper::changeTimeFormatForAttendanceView($this->meeting_start_time),
            'publish_date' => convertDateTimeFormat($this->meeting_published_at),
            'image' => $this->image ? asset(TeamMeeting::UPLOAD_PATH.$this->image) : '',
            'created_by' => $this->createdBy?->name,
            'creator' => new TeamSheetResource($this->createdBy ?? new User()),
            'participator' => new MeetingParticipatorCollection($this->teamMeetingParticipator)
        ];
    }
}


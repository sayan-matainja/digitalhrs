<?php

namespace App\Resources\Notification;

use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => (int)$this->id,
            'notification_title' => ucfirst($this->title),
            'description' => removeHtmlTags($this->description),
            'notification_published_date' => $this->notification_publish_date,
            'published_date_nepali' => AppHelper::formatDateForView($this->notification_publish_date). ', ' .date('h:i A',strtotime($this->notification_publish_date)),
            'type' => $this->type,
            'notification_for_id' => $this->notification_for_id ?? 0,
//            'is_seen' => $this->notifiedUsers->where('user_id',getAuthUserCode())->first()?->is_seen ?? 0
        ];
    }
}

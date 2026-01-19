<?php

namespace App\Resources\Notice;

use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class NoticeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'notice_title' => ucfirst($this->title),
            'description' => $this->description,
            'notice_published_date' => $this->notice_publish_date,
            'notice_published_date_nepali' => (AppHelper::formatDateForView($this->notice_publish_date)) . ',' . date("h:i A", strtotime($this->notice_publish_date)),
        ];
    }
}


<?php

namespace App\Resources\Complaint;

use App\Helpers\AppHelper;
use App\Resources\Warning\ResponseResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'message' => removeHtmlTags($this->message),
            'complaint_date' => AppHelper::formatDateForView($this->complaint_date),
            'response'=> $this->complaintReply->first() ? removeHtmlTags($this->complaintReply->first()->message)  : '',
        ];
    }
}


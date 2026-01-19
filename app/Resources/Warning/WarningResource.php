<?php

namespace App\Resources\Warning;

use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class WarningResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'message' => removeHtmlTags($this->message),
            'warning_date' => AppHelper::formatDateForView($this->warning_date),
            'response'=> $this->warningReply->first() ? removeHtmlTags($this->warningReply->first()->message)  : '',

        ];
    }
}


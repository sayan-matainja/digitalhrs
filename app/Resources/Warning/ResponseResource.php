<?php

namespace App\Resources\Warning;

use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'message' => removeHtmlTags($this->message),
        ];
    }
}


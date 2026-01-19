<?php

namespace App\Resources\StaticPageContent;

use Illuminate\Http\Resources\Json\JsonResource;

class StaticPageContentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'title' => $this->title,
            'content_type' => removeSpecialChars($this->content_type),
            'description' => $this->description,
        ];
    }
}













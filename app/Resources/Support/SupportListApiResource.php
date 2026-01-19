<?php

namespace App\Resources\Support;

use Illuminate\Http\Resources\Json\JsonResource;

class SupportListApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'title' => ucfirst($this->title),
            'description' => ($this->description),
            'query_date' => date('M d Y',strtotime($this->query_date)),
            'status' => ucwords(removeSpecialChar($this->status)),
            'requested_department' => ucfirst($this->requested_department).' '. __('index.department'),
            'updated_by' => ucfirst($this->updated_by) ?? '',
            'updated_at' => $this->updated_by ? date('M d Y',strtotime($this->updated_date)) : '',
        ];
    }
}

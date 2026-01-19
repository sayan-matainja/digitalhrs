<?php

namespace App\Resources\User;


use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'employee' => TeamSheetResource::collection($this->employee)
        ];
    }
}














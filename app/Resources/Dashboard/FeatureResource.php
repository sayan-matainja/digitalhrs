<?php

namespace App\Resources\Dashboard;

use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name ,
            'key' => $this->key ,
            'status' => $this->status,
        ];
    }
}












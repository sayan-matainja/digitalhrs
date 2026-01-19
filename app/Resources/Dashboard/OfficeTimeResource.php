<?php

namespace App\Resources\Dashboard;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeTimeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this?->id,
            'start_time' => $this?->officeTime?->opening_time,
            'end_time' => $this?->officeTime?->closing_time,
        ];
    }
}















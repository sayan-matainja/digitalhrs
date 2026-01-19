<?php

namespace App\Resources\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyWeekendResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this?->company?->id,
            'name' => $this?->company?->name,
            'weekend' => $this?->company?->weekend,
        ];
    }
}


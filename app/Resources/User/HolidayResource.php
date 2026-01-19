<?php

namespace App\Resources\User;


use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'event' => $this->event,
            'event_date' => $this->event_date ?? '',
            'nepali_date' => AppHelper::getFormattedAdDateToBs($this->event_date),
            'description' => $this->note ?? '',
            'is_public_holiday' => ($this->is_public_holiday == 1),
        ];
    }
}














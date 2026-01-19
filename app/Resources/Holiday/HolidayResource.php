<?php

/**
 * Created by PhpStorm.
 * User: sandeep
 * Date: 8/1/2021
 * Time: 5:20 PM
 */
namespace App\Resources\Holiday;

use App\Helpers\AppHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id ?? 0,
            'event' => $this->event ,
            'event_date' => $this->event_date ,
            'nepali_date' => AppHelper::getFormattedAdDateToBs($this->event_date),
            'description' => $this->note ?? '',
            'is_public_holiday' => ($this->is_public_holiday == 1),
        ];
    }
}












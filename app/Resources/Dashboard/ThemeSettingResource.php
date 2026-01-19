<?php

namespace App\Resources\Dashboard;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ThemeSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'primary-light' => $this->primary_color ?? '#ff3366',
            'primary-dark' => $this->dark_primary_color ?? '#ff3366',
        ];
    }
}















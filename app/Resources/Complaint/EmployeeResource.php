<?php

namespace App\Resources\Complaint;

use App\Enum\TrainerTypeEnum;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id ?? '',
            'name' => ucfirst($this->name) ?? '',

        ];
    }
}

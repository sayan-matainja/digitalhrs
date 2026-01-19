<?php

namespace App\Resources\Training;

use App\Enum\TrainerTypeEnum;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id ?? '',
            'name' => ucfirst($this->dept_name),
        ];
    }
}

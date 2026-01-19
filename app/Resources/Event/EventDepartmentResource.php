<?php

namespace App\Resources\Event;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EventDepartmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => ucfirst($this->department?->id) ?? '',
            'name' => ucfirst($this->department?->dept_name) ?? '',
            'is_active' => $this->department?->is_active,
            'department_head' => ($this->department?->departmentHead) ? $this->department?->departmentHead?->name : '',
        ];
    }
}

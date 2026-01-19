<?php

namespace App\Resources\Support;

use App\Models\Department;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportResource extends JsonResource
{
    public function toArray($request)
    {


        return [
            'id' => $this->id,
            'title' => ucfirst($this->title),
            'description' => ($this->description),
            'created_at' => date('M d Y',strtotime($this->created_at)),
            'created_by' => ($this->createdBy) ? ucfirst($this->createdBy->name) : '',
            'requested_department' => isset($this->departmentQuery)
                ? ucfirst($this->departmentQuery->dept_name)
                . (stripos($this->departmentQuery->dept_name, __('index.department')) !== false
                    ? ''
                    : ' ' . __('index.department'))
                : ''
        ];
    }
}

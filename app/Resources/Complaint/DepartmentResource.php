<?php

namespace App\Resources\Complaint;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => ucfirst($this->id) ?? '',
            'name' => ucfirst($this->dept_name) ?? '',
            'employee'=> isset( $this->employees) ?  $this->employees->map(function ($employee) {
                            return new EmployeeResource($employee);
                        }) : [],
        ];
    }
}

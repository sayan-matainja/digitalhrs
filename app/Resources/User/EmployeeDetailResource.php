<?php

namespace App\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name ?? '',
            'username' => $this->username,
            'phone' => $this->phone ?? '',
            'dob' => $this->dob ?? '' ,
            'gender' => ucfirst($this->gender) ?? '',
            'address' => ucfirst($this->address) ?? '',
            'employment_type' => removeSpecialChars($this->employment_type),
            'user_type' => removeSpecialChars($this->user_type),
            'branch' => ucfirst($this->branch?->name),
            'department' => ucfirst($this->department?->dept_name),
            'post' => ucfirst($this->post?->post_name),
            'avatar' => ($this->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$this->avatar)  : asset('assets/images/img.png'),
            'joining_date' => !is_null($this->joining_date) ? ($this->joining_date):'N/A',
            'awards' => $this->additional['awards'],
        ];
    }

}

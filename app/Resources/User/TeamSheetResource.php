<?php

namespace App\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class  TeamSheetResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id ?? 0,
            'name' => $this->name ?? 'Admin',
            'email' => $this->email ?? '',
            'username' => $this->username ?? 'admin',
            'phone' => $this->phone ?? '',
            'dob' => $this->dob ?? '',
            'gender' => $this->gender ?? '',
            'branch' => ucfirst($this->branch?->name),
            'department' => ucfirst($this->department?->dept_name),
            'post' => ucfirst($this->post?->post_name),
            'avatar' => ($this->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$this->avatar) : asset('assets/images/img.png'),
            'online_status' => $this->online_status ?? false,
            'joining_date' => !is_null($this->joining_date) ? ($this->joining_date):'N/A',
        ];

    }

}

<?php

namespace App\Resources\Event;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class EventUserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => ucfirst($this->employee->id) ?? '',
            'name' => ucfirst($this->employee->name) ?? '',
            'email' => $this->employee->email ?? '',
            'phone' => $this->employee->phone ?? '',
            'online_status' => $this->employee->online_status,
            'avatar' => isset($this->employee->avatar) ?  asset(User::AVATAR_UPLOAD_PATH.$this->employee->avatar) : asset('assets/images/img.png'),
            'post' => isset($this->employee->post) ? $this->employee->post->post_name : '',
        ];
    }
}

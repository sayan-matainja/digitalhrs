<?php

namespace App\Resources\AssignedMember;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignedMemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->user?->id,
            'name' => $this->user?->name,
            'avatar' => $this->user->avatar ? asset(User::AVATAR_UPLOAD_PATH.$this->user->avatar)  : asset('assets/images/img.png'),
            'post' => $this->user?->post?->post_name ?? ''
        ];
    }
}














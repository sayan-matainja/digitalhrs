<?php

namespace App\Resources\TeamMeeting;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMeetingParticipatorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => ucfirst($this->participator->id) ?? '',
            'name' => ucfirst($this->participator->name) ?? '',
            'email' => ($this->participator->email) ?? '',
            'phone' => ($this->participator->phone) ?? '',
            'online_status' => $this->participator->online_status,
            'avatar' => ($this->participator->avatar) ?  asset(User::AVATAR_UPLOAD_PATH.$this->participator->avatar) : asset('assets/images/img.png'),
            'post' => ($this->participator->post) ? $this->participator->post->post_name : '',
        ];
    }
}

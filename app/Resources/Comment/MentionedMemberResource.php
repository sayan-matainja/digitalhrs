<?php

namespace App\Resources\Comment;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class MentionedMemberResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->member_id,
            'name' => $this->user?->name,
            'username' => $this?->user?->username,
        ];

    }
}

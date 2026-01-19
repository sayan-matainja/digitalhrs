<?php

namespace App\Resources\Comment;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ReplyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'reply_id' => $this->id,
            'description' => $this->description,
            'comment_id' => $this->comment_id,
            'created_by_id' => $this->created_by ?? '0',
            'created_by_name' => $this->createdBy->name ?? 'Admin',
            'username' => $this->createdBy->username ?? 'admin',
            'avatar' => isset($this->createdBy->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$this->createdBy->avatar) : asset('assets/images/img.png'),
            'created_at' => $this->created_at->diffForHumans(),
            'mentioned' => new MentionedMemberCollection($this->mentionedMember)
        ];
    }
}

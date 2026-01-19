<?php

namespace App\Resources\Comment;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskCommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'task_id' => $this->task_id ?? '',
            'comment_id' => $this->comment_id ?? '',
            'created_by' => $this->createdBy->name ?? 'Admin',
            'avatar' => isset($this->createdBy->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$this->createdBy->avatar) : asset('assets/images/img.png'),
            'created_at' => $this->created_at->diffForHumans(),
            'mentioned' => new MentionedMemberCollection($this->mentionedMember),
        ];
    }
}

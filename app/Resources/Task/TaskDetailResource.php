<?php

namespace App\Resources\Task;

use App\Helpers\PMHelper;
use App\Resources\AssignedMember\AssignedMemberCollection;
use App\Resources\Attachment\AttachmentCollection;
use App\Resources\Comment\CommentWithRepliesCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'task_id' => $this->id,
            'task_name' => $this?->name,
            'project_name' => $this->project->name,
            'start_date' => date('M d Y', strtotime($this?->start_date)),
            'deadline' => date('M d Y', strtotime($this->end_date)),
            'description' => $this?->description,
            'status' => PMHelper::STATUS[$this?->status],
            'priority' => ucfirst($this?->priority),
            'assigned_member' => new AssignedMemberCollection($this?->assignedMembers),
            'checklists' => $this->whenLoaded('taskAssignedChecklists'),
            'has_checklist' => count($this?->taskChecklists) > 0,
            'task_progress_percent' => $this->getTaskProgressInPercentage(),
            'attachments' => count($this->taskAttachments) > 0 ? new AttachmentCollection($this->taskAttachments) : [],
            'task_comments' => count($this->taskComments) > 0 ? new CommentWithRepliesCollection($this->taskComments) : []
        ];

    }
}














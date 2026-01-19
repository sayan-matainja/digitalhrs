<?php

namespace App\Resources\Task;

use App\Helpers\PMHelper;
use App\Resources\AssignedMember\AssignedMemberCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskListingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'task_id' => $this->id,
            'project_name' => $this?->project?->name,
            'task_name' => $this?->name,
            'start_date' => date('M d Y', strtotime($this?->start_date)),
            'deadline' => date('M d Y', strtotime($this->end_date)),
            'status' => PMHelper::STATUS[$this?->status],
            'priority' => ucfirst($this?->priority),
            'assigned_member' => new AssignedMemberCollection($this?->assignedMembers),
            'assigned_checklists_count' => $this->taskAssignedChecklists->count(),
            'task_progress_percent' => (isset( $this->status) && $this->status == 'completed') ? 100 :$this->getTaskProgressInPercentage(),
        ];
    }
}


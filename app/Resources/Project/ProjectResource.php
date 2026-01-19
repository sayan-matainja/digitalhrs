<?php

namespace App\Resources\Project;

use App\Helpers\PMHelper;
use App\Models\Project;
use App\Resources\AssignedMember\AssignedMemberCollection;
use App\Resources\Attachment\AttachmentCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'client_name' => $this->client->name,
            'start_date' => $this->start_date,
            'deadline' => $this->deadline,
            'status' => PMHelper::STATUS[$this->status],
            'priority' => ucfirst($this->priority),
            'description' => ($this->description),
            'cover_pic' => $this->cover_pic ? asset(Project::UPLOAD_PATH.$this->cover_pic) : asset('assets/images/img.png'),
            'assigned_member' => new AssignedMemberCollection($this?->assignedMembers),
            'project_leader' => new AssignedMemberCollection($this?->projectLeaders),
            'assigned_task_detail' => $this->whenLoaded('getOnlyEmployeeAssignedTask') ?  new ProjectTaskCollection($this->getOnlyEmployeeAssignedTask) : '',
            'assigned_task_count' => $this->getOnlyEmployeeAssignedTask->count(),
            'progress_percent' => $this->getProjectProgressInPercentage()
        ];
        if($this->whenLoaded('projectAttachments')){
            $data['attachments'] = new AttachmentCollection($this->projectAttachments);
        }

        return $data;

    }
}













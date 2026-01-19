<?php

namespace App\Resources\Project;

use App\Helpers\PMHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'task_id' => $this?->id,
            'task_name' => $this?->name,
            'start_date' => date('M d Y', strtotime($this?->start_date)),
            'deadline' => date('M d Y', strtotime($this->end_date)),
            'status' => PMHelper::STATUS[$this?->status],
            'priority' => ucfirst($this?->priority),
            'task_progress_percent' => $this->getTaskProgressInPercentage(),
        ];
    }
}

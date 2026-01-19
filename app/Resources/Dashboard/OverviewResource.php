<?php

namespace App\Resources\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class OverviewResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'present_days' => ($this->total_present_day) ? (int)($this->total_present_day):0,
            'total_paid_leaves' => ($this->total_paid_leaves) ? (int)($this->total_paid_leaves):0,
            'total_holidays' => ($this->total_holidays) ? (int)($this->total_holidays): 0,
            'total_pending_leaves' => ($this->total_pending_leaves) ? (int)($this->total_pending_leaves):0, // deprecated
            'total_leave_taken' => ($this->total_leave_taken) ? (int)($this->total_leave_taken):0, // deprecated
            'total_approved_leave' => ($this->total_leave_taken) ? (double)($this->total_leave_taken):0,
            'total_pending_leave' => ($this->total_pending_leaves) ? (double)($this->total_pending_leaves):0,
            'total_assigned_projects' => ($this->total_projects) ? (int)($this->total_projects):0,
            'total_pending_tasks' => ($this->total_pending_tasks) ? (int)($this->total_pending_tasks):0,
            'total_awards' => ($this->total_awards) ? (int)($this->total_awards):0,
            'active_event' => ($this->upcoming_events) ? (int)($this->upcoming_events):0,
            'active_training' => ($this->upcoming_training) ? (int)($this->upcoming_training):0,
        ];
    }
}














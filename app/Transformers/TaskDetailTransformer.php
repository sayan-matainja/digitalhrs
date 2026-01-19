<?php

namespace App\Transformers;

use App\Helpers\PMHelper;
use App\Resources\AssignedMember\AssignedMemberCollection;

class TaskDetailTransformer
{
    public $employeeProjectDetail;

    public function __construct($employeeProjectDetail)
    {
        $this->employeeProjectDetail = $employeeProjectDetail;
    }

    public function transform($limitParams): array
    {
        $assignedProjectDetail = [];
        $assignedTaskDetail = [];
        $assignedTaskCompletedDetail = [];

        $totalProjectProgress = 0;

        foreach ($this->employeeProjectDetail as $key => $projectDetail) {
            $totalProjectProgress += $projectDetail->getProjectProgressInPercentage();
            $assignedProjectDetail[] = [
                'id' => $projectDetail->id,
                'project_name' => $projectDetail->name,
                'start_date' => date('M d Y', strtotime($projectDetail->start_date)),
                'end_date' => date('M d Y', strtotime($projectDetail->deadline)),
                'status' => PMHelper::STATUS[$projectDetail->status],
                'priority' => ucfirst($projectDetail->priority),
                'assigned_member' => new AssignedMemberCollection($projectDetail->assignedMembers),
                'project_progress_percent' => $projectDetail->getProjectProgressInPercentage(),
                'assigned_task_count' => $projectDetail->getOnlyEmployeeAssignedTask->count()
            ];
            if ($projectDetail->getOnlyEmployeeAssignedTask) {
                foreach ($projectDetail->getOnlyEmployeeAssignedTask as $key => $value) {
                    if ($value->status == 'completed') {
                        $assignedTaskCompletedDetail[] = [
                            'task_id' => $value->id
                        ];
                    }
                    $assignedTaskDetail[] = [
                        'project_name' => $value->project->name,
                        'task_id' => $value->id,
                        'task_name' => $value->name,
                        'start_date' => date('M d Y', strtotime($value->start_date)),
                        'end_date' => date('M d Y', strtotime($value->end_date)),
                        'status' => PMHelper::STATUS[$value->status],
                        'priority' => ucfirst($value->priority),
                        'assigned_member' => new AssignedMemberCollection($value->assignedMembers),
                        'task_progress_percent' => $value->getTaskProgressInPercentage(),
                    ];
                }
            }
        }

        if (count($assignedTaskDetail) > 0) {
            $totalAssignedTask = count($assignedTaskDetail);
            $totalCompletedTask = count($assignedTaskCompletedDetail);
            $progress = ceil($totalCompletedTask / $totalAssignedTask * 100);
        } else {
            $progress = 0;
        }

        $progressDetail = [
            'total_task_assigned' => $totalAssignedTask ?? 0,
            'total_task_completed' => $totalCompletedTask ?? 0,
            'progress_in_percent' => $progress,
        ];

        $totalProjects = count($this->employeeProjectDetail) ?? 0;
        $completedProject = $this->employeeProjectDetail->where('status','=','completed')->count() ?? 0;
        $projectProgress = $totalProjects > 0 ? ceil($totalProjectProgress / $totalProjects)  : 0;
        $projectProgressDetail = [
            'total_project_assigned' => $totalProjects,
            'total_project_completed' => $completedProject,
            'progress_in_percent' => $projectProgress,
        ];
        return [
            'assigned_projects' => array_slice($assignedProjectDetail, 0, $limitParams['projects']),
            'assigned_task' => array_slice($assignedTaskDetail, 0, $limitParams['tasks']),
            'progress_report' => $progressDetail,
            'project_progress_report' => $projectProgressDetail,
        ];
    }

}


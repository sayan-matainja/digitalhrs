<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\Project\ProjectService;

class TaskObserver
{
    public ProjectService $projectService;

    public function __construct(ProjectService $projectService){
        $this->projectService = $projectService;
    }

    public function created(Task $task)
    {
       try{
           $this->updateProjectProgressStatusBasedOnChangesOnTask($task->project_id);
       }catch(\Exception $exception){
         throw $exception;
       }
    }

    public function updated(Task $task)
    {
        try{
            $this->updateProjectProgressStatusBasedOnChangesOnTask($task->project_id);
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    public function deleted(Task $task)
    {
        try{
            $this->updateProjectProgressStatusBasedOnChangesOnTask($task->project_id);
        }catch(\Exception $exception){
            throw $exception;
        }
    }

    private function updateProjectProgressStatusBasedOnChangesOnTask($projectId)
    {
        $projectDetail = $this->projectService->findProjectDetailById($projectId);
        $this->projectService->updateProjectProgressStatus($projectDetail);
    }

}

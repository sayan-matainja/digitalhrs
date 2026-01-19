<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Services\Project\ProjectService;
use App\Transformers\TaskDetailTransformer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectManagementDashboardApiController
{
    private ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function getUserProjectManagementDashboardDetail(Request $request): JsonResponse
    {
        try {
            $limitParams = [
                'projects' => ($request->projects) ?? 3 ,
                'tasks' => ($request->tasks) ?? 5 ,
            ];
            $select = ['*'];
            $with = [
                'assignedMembers.user:id,name,avatar',
                'getOnlyEmployeeAssignedTask.assignedMembers.user:id,name,avatar',
            ];
            $dashboard = [];
            $employeeProjectDetail = $this->projectService->getAllActiveProjectsOfEmployee(getAuthUserCode(),$select,$with);
            $transformedDetail = (new TaskDetailTransformer($employeeProjectDetail))->transform($limitParams);
            $dashboard['projects'] = $transformedDetail['assigned_projects'];
            $dashboard['assigned_task'] = $transformedDetail['assigned_task'];
            $dashboard['progress'] = $transformedDetail['progress_report'];
            $dashboard['project_progress'] = $transformedDetail['project_progress_report'];
            return AppHelper::sendSuccessResponse(__('index.data_found'), $dashboard);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

}

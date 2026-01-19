<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Resources\Project\ProjectCollection;
use App\Resources\Project\ProjectResource;
use App\Services\Project\ProjectService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class ProjectApiController extends Controller
{
    private ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function getUserAssignedAllProjects(Request $request)
    {
        try {

            $perPage = $request->per_page ?? Project::RECORDS_PER_PAGE;
            $select = ['*'];
            $with = [
                'assignedMembers.user.post:id,post_name',
                'client:id,name'
            ];
            $projectLists = $this->projectService->getAllActiveProjectOfEmployeePaginated(getAuthUserCode(),$perPage,$select,$with);


            return new ProjectCollection($projectLists);
        }catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

    public function getProjectDetailById($projectId): JsonResponse
    {
        try {
            $select = ['*'];
            $with = [
                'getOnlyEmployeeAssignedTask:project_id,name,id,status,priority,start_date,end_date,is_active',
                'assignedMembers.user:id,name,avatar,post_id',
                'assignedMembers.user.post:id,post_name',
                'client:id,name',
                'projectAttachments',
                'projectLeaders.user.post:id,post_name'
            ];
            $detail = $this->projectService->findAssignedMemberProjectDetailById($projectId,$with,$select);
            $projectDetail = new ProjectResource($detail);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$projectDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }

}

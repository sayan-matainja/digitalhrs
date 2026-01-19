<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Services\Task\TaskChecklistService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Exception;

class TaskChecklistApiController extends Controller
{
    use CustomAuthorizesRequests;
    private TaskChecklistService $taskChecklistService;

    public function __construct(TaskChecklistService $taskChecklistService)
    {
        $this->taskChecklistService = $taskChecklistService;
    }

    /**
     * @throws AuthorizationException
     */
    public function toggleCheckListIsCompletedStatus($checklistId): JsonResponse
    {
        $this->authorize('toggle_checklist_status');

        try {
            $checkList = $this->taskChecklistService->toggleIsCompletedStatusByAssignedUserOnly($checklistId);
            return AppHelper::sendSuccessResponse(__('index.status_updated_successfully'),$checkList);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), 400);
        }
    }
}

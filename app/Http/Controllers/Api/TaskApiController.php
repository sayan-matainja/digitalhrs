<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Resources\Comment\CommentWithRepliesCollection;
use App\Resources\Task\TaskCollection;
use App\Resources\Task\TaskDetailResource;
use App\Services\Task\TaskChecklistService;
use App\Services\Task\TaskCommentService;
use App\Services\Task\TaskService;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskApiController extends Controller
{
    use CustomAuthorizesRequests;
    public TaskService $taskService;
    public TaskChecklistService $taskChecklistService;
    public TaskCommentService $commentService;

    public function __construct(TaskService $taskService,TaskChecklistService $taskChecklistService,TaskCommentService $commentService )
    {
        $this->taskService = $taskService;
        $this->taskChecklistService = $taskChecklistService;
        $this->commentService = $commentService;
    }

    public function getUserAssignedAllTasks(Request $request)
    {
        try {
            $perPage = $request->get('per_page') ?? 20;
            $select = ['*'];
            $with = [
                'taskAssignedChecklists:task_id,id',
                'assignedMembers.user:id,name,avatar',
                'project:id,name'
            ];
            $detail = $this->taskService->getUserAssignedAllTasks(getAuthUserCode(),$select,$with,$perPage);


            return new TaskCollection($detail);
         }catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getTaskDetailById($taskId)
    {
        try {
            $select = ['*'];
            $with = [
                'taskChecklists:task_id,id',
                'taskAssignedChecklists:task_id,name,id,is_completed',
                'assignedMembers.user:id,name,avatar,post_id',
                'assignedMembers.user.post:id,post_name',
                'project:id,name',
                'taskAttachments',
                'taskComments.replies',
                'taskComments.replies.mentionedMember.user:id,name',
                'taskComments.mentionedMember.user:id,name,avatar',
                'taskComments.createdBy:id,name,avatar',
            ];
            $detail = $this->taskService->findAssignedMemberTaskDetailById($taskId,$with,$select);
            $taskDetail = new TaskDetailResource($detail);
            return AppHelper::sendSuccessResponse(__('index.data_found'),$taskDetail);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function changeTaskStatus($taskId): JsonResponse
    {
        $this->authorize('edit_task_status');
        try {

            $with = ['taskChecklists'];
            $taskDetail = $this->taskService->findAssignedMemberTaskDetailById($taskId,$with);
            if(!$taskDetail){
                throw new Exception(__('index.task_detail_not_found'),404);
            }
            $status = $this->taskService->changeStatus($taskDetail);
            return AppHelper::sendSuccessResponse(__('index.status_changed_successfully'),$status);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    public function getTaskComments(Request $request): JsonResponse|CommentWithRepliesCollection
    {
        try {
            $filterParameters = [
               'per_page' =>  $request->get('per_page') ?? 20,
               'task_id' =>  $request->get('task_id'),
           ];
            $select = ['*'];
            $with=[
                'replies',
                'replies.mentionedMember.user:id,name,username',
                'mentionedMember.user:id,name,avatar,username',
                'createdBy:id,name,avatar,username'
            ];
            $comments = $this->commentService->getAllTaskCommentsByTaskId($filterParameters,$select,$with);
            return new CommentWithRepliesCollection($comments);
        }catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}


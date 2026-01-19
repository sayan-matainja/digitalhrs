<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Http\Controllers\Controller;
use App\Requests\Task\TaskCommentRequest;
use App\Resources\Comment\CommentWithReplyResource;
use App\Resources\Comment\ReplyResource;
use App\Services\Notification\NotificationService;
use App\Services\Task\TaskCommentService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Exception;

class TaskCommentApiController extends Controller
{
    use CustomAuthorizesRequests;
    public TaskCommentService $commentService;
    public NotificationService $notificationService;


    public function __construct(TaskCommentService $commentService,NotificationService $notificationService)
    {
        $this->commentService = $commentService;
        $this->notificationService = $notificationService;
    }


    /**
     * @throws AuthorizationException
     */
    public function saveComment(TaskCommentRequest $request): JsonResponse
    {
        $this->authorize('submit_comment');
        try {

            $validatedData = $request->validated();
            $mentionedMember = $validatedData['mentioned'] ?? [];
            DB::beginTransaction();
            if (is_null($validatedData['comment_id'])) {
                $data = $this->commentService->storeTaskCommentDetail($validatedData);
                $commentType = 'comment';
                $taskName = $data?->task?->name;
                if($data?->task?->created_by != getAuthUserCode()){
                    $mentionedMember[] =  $data?->task?->created_by;
                }
                $detail = new CommentWithReplyResource($data);
            } else {
                $data = $this->commentService->storeCommentReply($validatedData);
                $comment = $data->comment;
                $commentType = __('index.comment_reply');
                $taskName = $comment->task?->name;
                if($comment->created_by != getAuthUserCode()){
                    $mentionedMember[] = $comment->created_by;
                }
                if($comment->task?->created_by != getAuthUserCode()){
                    $mentionedMember[] = $comment->task?->created_by;
                }
                $detail = new CommentWithReplyResource($comment);
            }
            DB::commit();
            if (count($mentionedMember) > 0) {
                $mentionedMember = array_unique($mentionedMember);
                $notificationData = [
                    'title' => __('index.comment_notification'),
                    'type' => 'comment',
                    'user_id' => $mentionedMember,
                    'description' => __('index.task_mention', ['task' => $taskName, 'type' => $commentType,]) ,
                    'notification_for_id' => $validatedData['task_id'],
                ];
                $notification = $this->notificationService->store($notificationData);
                if($notification){
                    $this->sendNotificationToMentionedMemberInComment(
                        $notificationData['title'],
                        $notificationData['description'],
                        $notificationData['user_id'],
                        $notificationData['notification_for_id']
                    );
                }
            }
            return AppHelper::sendSuccessResponse(__('index.comment_added_successfully'), $detail);
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    private function sendNotificationToMentionedMemberInComment($title,$message, $userIds, $taskId)
    {
        SMPushHelper::sendProjectManagementNotification($title, $message, $userIds, $taskId);
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteComment($commentId): JsonResponse
    {
        $this->authorize('comment_delete');
        try {

            DB::beginTransaction();
                $this->commentService->deleteTaskComment($commentId);
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.comment_deleted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function deleteReply($replyId): JsonResponse
    {
        $this->authorize('reply_delete');
        try {

            DB::beginTransaction();
            $this->commentService->deleteReply($replyId);
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.comment_reply_deleted_successfully'));
        } catch (Exception $exception) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($exception->getMessage(), $exception->getCode());
        }
    }

}

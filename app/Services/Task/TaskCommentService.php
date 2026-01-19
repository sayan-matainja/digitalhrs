<?php

namespace App\Services\Task;

use App\Helpers\AppHelper;
use App\Repositories\TaskCommentRepository;
use App\Repositories\TaskRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TaskCommentService
{
    public TaskCommentRepository $commentRepo;
    public TaskRepository $taskRepo;

    public function __construct(TaskCommentRepository $commentRepo,
                                TaskRepository        $taskRepo)
    {
        $this->commentRepo = $commentRepo;
        $this->taskRepo = $taskRepo;
    }

    public function getAllTaskCommentsByTaskId($filterParameters,$select=['*'],$with=[])
    {
        try{
            return $this->commentRepo->getCommentsPaginatedByTaskId($filterParameters,$select,$with);
        }catch(Exception $exception){
            throw $exception;
        }
    }

    /**
     * @throws Exception
     */
    public function storeTaskCommentDetail($validatedData)
    {

        $this->canCommentOnTask($validatedData['task_id']);

        $comment = $this->commentRepo->storeComment($validatedData);
        if ($comment) {
            if (isset($validatedData['mentioned'])) {
                $mentionedData = $this->prepareDataForMentionMember($validatedData['mentioned']);
                $this->commentRepo->createMentionedMemberInComment($comment, $mentionedData);
            }
        }

        return $comment;

    }

    /**
     * @throws Exception
     */
    private function canCommentOnTask($taskId): void
    {

        if (isset(auth('admin')->user()->id)) {
            return;
        }
        $taskDetail = $this->taskRepo->findAssignedMemberTaskDetailById($taskId, $with = [], ['*']);
        if (!$taskDetail) {
            throw new Exception(__('message.cannot_comment'), 403);
        }

    }

    private function prepareDataForMentionMember($mentionedData): array
    {

        $mentionedArray = [];
        foreach ($mentionedData as $key => $value) {
            $mentionedArray[$key]['member_id'] = $value;
        }
        return $mentionedArray;

    }

    /**
     * @throws Exception
     */
    public function storeCommentReply($validatedData)
    {
        $this->canCommentOnTask($validatedData['task_id']);

        $reply = $this->commentRepo->storeCommentReply($validatedData);
        if (isset($validatedData['mentioned'])) {
            $mentionedData = $this->prepareDataForMentionMember($validatedData['mentioned']);
            $this->commentRepo->createMentionedMemberInReply($reply, $mentionedData);
        }

        return $reply;
    }

    /**
     * @throws Exception
     */
    public function deleteTaskComment($commentId)
    {

        $commentDetail = $this->findCommentDetailById($commentId);

        $admin = auth('admin')->user();

        if ($admin && isset($admin->id)) {
            return $this->commentRepo->deleteComment($commentDetail);
        }

        if ($commentDetail->created_by !== null) {
            if (auth()->user()->id == $commentDetail->created_by) {
                return $this->commentRepo->deleteComment($commentDetail);
            }
            throw new Exception(__('index.cannot_delete_comment'), 403);
        }

        throw new Exception(__('index.cannot_delete_comment'), 403);

    }

    /**
     * @throws Exception
     */
    public function findCommentDetailById($id, $with = [], $Select = ['*'])
    {
        return $this->commentRepo->findCommentById($id, $with, $Select);
    }

    /**
     * @throws Exception
     */
    public function deleteReply($replyId)
    {

        $replyDetail = $this->findReplyDetailById($replyId);

        $admin = auth('admin')->user();

        if ($admin && isset($admin->id)) {
            return $this->commentRepo->deleteReply($replyDetail);
        }

        if ($replyDetail->created_by !== null) {
            if (auth()->user()->id == $replyDetail->created_by) {
                return $this->commentRepo->deleteReply($replyDetail);
            }
            throw new Exception(__('index.cannot_delete_reply'), 403);
        }

        throw new Exception(__('index.cannot_delete_reply'), 403);

    }

    /**
     * @throws Exception
     */
    public function findReplyDetailById($id, $with = [], $Select = ['*'])
    {
        return $this->commentRepo->findCommentReplyById($id, $with, $Select);
    }

}

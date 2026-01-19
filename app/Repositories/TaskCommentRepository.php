<?php

namespace App\Repositories;

use App\Models\CommentReply;
use App\Models\TaskComment;

class TaskCommentRepository
{

    public function getAllCommentWithReply($select=['*'],$with=[])
    {
        return TaskComment::with($with)->select($select)->get();
    }

    public function getCommentsPaginatedByTaskId($filterParameters,$select=['*'],$with=[])
    {
        return TaskComment::with($with)
            ->select($select)
            ->where('task_id',$filterParameters['task_id'])
            ->latest()
            ->paginate($filterParameters['per_page']);
    }

    public function getAllTaskCommentForTaskAssignedMember($userId,$select=['*'],$with=[])
    {
        return TaskComment::query()->select($select)>with($with)
                ->whereHas('mentionedMember.user',function($subQuery) use ($userId){
                    $subQuery->where('id', $userId);
                })
            ->latest()
            ->get();
    }

    public function findCommentById($id,$with=[],$select=['*'])
    {
        return TaskComment::with($with)->select($select)
            ->where('id',$id)
            ->first();
    }

    public function findCommentReplyById($id,$with=[],$select=['*'])
    {
        return CommentReply::with($with)->select($select)
            ->where('id',$id)
            ->first();
    }

    public function storeComment($validatedData)
    {
        return TaskComment::create($validatedData)->fresh();
    }

    public function storeCommentReply($validatedData)
    {
       return CommentReply::create($validatedData)->fresh();
    }

    public function createMentionedMemberInComment(TaskComment $commentDetail,$mentionedArray)
    {
        return $commentDetail->mentionedMember()->createMany($mentionedArray);
    }

    public function createMentionedMemberInReply(CommentReply $replyDetail, $mentionedArray)
    {
        return $replyDetail->mentionedMember()->createMany($mentionedArray);
    }

    public function deleteComment($commentDetail)
    {
        return $commentDetail->delete();
    }

    public function deleteReply($commentReplyDetail)
    {
        return $commentReplyDetail->delete();
    }

}

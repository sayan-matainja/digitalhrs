<?php

namespace App\Services\Task;

use App\Helpers\PMHelper;
use App\Repositories\AttachmentRepository;
use App\Repositories\TaskRepository;
use App\Traits\ImageService;
use Exception;
use Illuminate\Support\Facades\DB;

class TaskService
{
    use ImageService;

    private TaskRepository $taskRepo;
    private AttachmentRepository $attachmentRepo;

    public function __construct(TaskRepository $taskRepo, AttachmentRepository $attachmentRepo)
    {
        $this->taskRepo = $taskRepo;
        $this->attachmentRepo = $attachmentRepo;
    }

    public function getAllFilteredTasksPaginated($filterParameter, $select = ['*'], $with = [])
    {
        return $this->taskRepo->getAllFilteredTasks($filterParameter, $select, $with);
    }

    public function getTaskDataForPieChart()
    {
        $taskDetail =  $this->taskRepo->getAllTaskDetailForPieChart();
        return [
            'not_started' => $taskDetail['not_started'] ?? 0,
            'on_hold' => $taskDetail['on_hold'] ?? 0,
            'in_progress' => $taskDetail['in_progress'] ?? 0,
            'completed' => $taskDetail['completed'] ?? 0,
            'cancelled' => $taskDetail['cancelled'] ?? 0
        ];
    }

    public function getAllTasks($select = ['*'])
    {
        return $this->taskRepo->getAllTasks($select);
    }

    public function getUserAssignedAllTasks($userId, $select, $with, $perPage)
    {
        return $this->taskRepo->getUserAssignedTasksLists($userId, $select, $with, $perPage);
    }

    public function findAssignedMemberTaskDetailById($taskId, $with = [], $select = ['*'])
    {
        try {
            $detail = $this->taskRepo->findAssignedMemberTaskDetailById($taskId, $with, $select);
            if (!$detail) {
                throw new Exception('Task Detail Not Found', 404);
            }
            return $detail;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function saveTaskDetail($validatedData)
    {
        try {
            $assignedMemberArray = [];
            foreach ($validatedData['assigned_member'] as $key => $value) {
                $assignedMemberArray[$key]['member_id'] = $value;
            }

            DB::beginTransaction();
            $task = $this->taskRepo->store($validatedData);
            if (!$task) {
                throw new Exception('Something went wrong.', 400);
            }

            if (isset($validatedData['attachments'])) {
                $taskAttachmentValidatedData = $this->attachmentRepo->prepareAttachmentData($validatedData['attachments']);
                $this->attachmentRepo->saveTaskAttachment($task, $taskAttachmentValidatedData);
            }
            $this->taskRepo->assignMemberToTask($task, $assignedMemberArray);
            DB::commit();
            return $task;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTaskDetail($validatedData, $projectId)
    {
        try {
            $with = ['taskAttachments'];
            $taskDetail = $this->findTaskDetailById($projectId, $with);
            if (!$taskDetail) {
                throw new Exception('Task Detail Not Found', 404);
            }

            DB::beginTransaction();
            $updateStatus = $this->taskRepo->update($taskDetail, $validatedData);
            if (!$updateStatus) {
                throw new Exception('Something went wrong !', 400);
            }
            if (isset($validatedData['attachments'])) {
                $updatedAttachmentData = $this->attachmentRepo->prepareAttachmentData($validatedData['attachments']);
                $this->attachmentRepo->saveTaskAttachment($taskDetail, $updatedAttachmentData);
            }
            $assignedMemberArray = [];
            foreach ($validatedData['assigned_member'] as $key => $value) {
                $assignedMemberArray[$key]['member_id'] = $value;
            }
            $this->taskRepo->assignMemberToTask($taskDetail, $assignedMemberArray);

            DB::commit();
            return $taskDetail;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findTaskDetailById($id, $with = [], $select = ['*'])
    {
        return $this->taskRepo->findTaskDetailById($id, $with, $select);
    }

    public function deleteTaskDetail($id): bool
    {
        try {
            $with = ['taskAttachments'];
            $taskDetail = $this->findTaskDetailById($id, $with);
            if (!$taskDetail) {
                throw new Exception('Task Detail Not Found', 404);
            }
            DB::beginTransaction();
            if (count($taskDetail->taskAttachments) > 0) {
                $this->attachmentRepo->removeOldAttachments($taskDetail->taskAttachments);
            }
            $status = $this->taskRepo->delete($taskDetail);
            DB::commit();
            return $status;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function toggleStatus($id): bool
    {
        try {
            DB::beginTransaction();
            $taskDetail = $this->findTaskDetailById($id);
            $this->taskRepo->toggleStatus($taskDetail);
            DB::commit();
            return true;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function changeStatus($taskDetail)
    {
        try {
            $completedStatus = 'completed';
            $notCompletedStatus = 'in_progress';
            DB::beginTransaction();
            if (count($taskDetail->taskChecklists) > 0) {
                throw new Exception('Task with checklists status cannot be changed', 403);
            }
            $taskStatus = ($taskDetail->status != 'completed') ? $completedStatus : $notCompletedStatus;
            $this->taskRepo->changeTaskStatus($taskDetail, $taskStatus);
            DB::commit();
            return [
                'id' => $taskDetail->id,
                'completed_status' => ($taskDetail->status == 'completed'),
                'status' => PMHelper::STATUS[$taskDetail?->status],
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function getProjectTasks($projectId, $select=['*'])
    {
        return $this->taskRepo->getTasksByProject($projectId, $select);
    }

    public function updateMemberOfTask($taskDetail,$validatedData)
    {
        try{
            $assignedMemberArray = [];
            foreach ($validatedData['employee'] as $value) {
                $assignedMemberArray[] = ['member_id' => $value];
            }
            return $this->taskRepo->updateTaskMember($taskDetail,$assignedMemberArray);
        }catch(Exception $e){
            throw $e;
        }
    }

}

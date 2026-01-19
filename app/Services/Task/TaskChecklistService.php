<?php

namespace App\Services\Task;

use App\Helpers\AppHelper;
use App\Helpers\SMPush\SMPushHelper;
use App\Repositories\TaskChecklistRepository;
use App\Repositories\TaskRepository;
use App\Services\Notification\NotificationService;
use Exception;
use Illuminate\Support\Facades\DB;

class TaskChecklistService
{
    private TaskChecklistRepository $taskChecklistRepo;
    private TaskRepository $taskRepo;
    public NotificationService $notificationService;


    public function __construct(TaskChecklistRepository $taskChecklistRepo,TaskRepository $taskRepo, NotificationService $notificationService)
    {
        $this->taskChecklistRepo = $taskChecklistRepo;
        $this->taskRepo = $taskRepo;
        $this->notificationService = $notificationService;
    }

    public function saveTaskCheckLists($validatedData)
    {
        try {
            $notCompletedStatus = 'in_progress';
            $checklists = [];
            DB::beginTransaction();

            foreach ($validatedData['name'] as $key => $value) {

                $checklists[$key]['task_id'] = $validatedData['task_id'];
                $checklists[$key]['name'] = $value;
                $checklists[$key]['assigned_to'] = $validatedData['assigned_to'][$key];

                $notificationData['title'] = 'Task Assignment Notification';
                $notificationData['type'] = 'task checklist';
                $notificationData['user_id'] = [$validatedData['assigned_to'][$key]];
                $notificationData['description'] = 'You are assigned to a new task checklist ' . $value ;
                $notificationData['notification_for_id'] =$key;
                $notification = $this->notificationService->store($notificationData);
                if ($notification) {
                    $this->sendNotificationToTaskCheckListAssignedMember(
                        $notification->title,
                        $notification->description,
                        $notificationData['user_id'],
                        $key);
                }

            }
            $checkListsInsertStatus = $this->taskChecklistRepo->createManyChecklist($checklists);
            if($checkListsInsertStatus){
                $taskDetail = $this->taskRepo->findTaskDetailById($validatedData['task_id']);
                 $this->taskRepo->changeTaskStatus($taskDetail,$notCompletedStatus);
            }
            DB::commit();
            return $checkListsInsertStatus;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function findTaskChecklistDetail($checklistId, $select = ['*'], $with = [])
    {
        return $this->taskChecklistRepo->findTaskChecklistDetailById($checklistId, $select, $with);
    }

    /**
     * @throws Exception
     */
    public function findTaskChecklistOfAssignedUserById($userId, $checklistId, $select=['*'], $with=[])
    {
        return $this->taskChecklistRepo->findTaskChecklistOfAssignedUserById($userId,$checklistId,$select,$with);
    }

    public function updateTaskChecklistDetail($validatedData, $taskChecklistId)
    {
        try {
            $checklistDetail = $this->findTaskChecklistDetail($taskChecklistId);
            DB::beginTransaction();
            $update = $this->taskChecklistRepo->update($checklistDetail, $validatedData);
            DB::commit();
            return $update;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
    /**
     * @throws Exception
     */
    public function deleteTaskChecklistDetail($id)
    {
        try {
            $completedStatus = 'completed';
            $taskChecklistDetail = $this->findTaskChecklistDetail($id);

            DB::beginTransaction();
            $deleteStatus = $this->taskChecklistRepo->delete($taskChecklistDetail);
            if($deleteStatus){
                if(intval($taskChecklistDetail->task->getTaskProgressInPercentage()) == 100){
                    $this->taskRepo->changeTaskStatus($taskChecklistDetail->task,$completedStatus);
                }
            }
            DB::commit();
            return $deleteStatus;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function toggleIsCompletedChecklistStatus($id)
    {
        try {
            $taskChecklistDetail = $this->findTaskChecklistDetail($id);
            DB::beginTransaction();
                $status = $this->taskChecklistRepo->toggleIsCompletedStatus($taskChecklistDetail);
                if($status){
                    $this->changeTaskStatus($taskChecklistDetail);
                }
            DB::commit();
            return $status;
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    public function toggleIsCompletedStatusByAssignedUserOnly($id)
    {
        try {
            $select = ['id','is_completed','name','task_id'];
            $with = ['task:id,name,status,project_id'];
            $userAssignedChecklist = $this->findTaskChecklistOfAssignedUserById(getAuthUserCode(),$id,$select,$with);
            DB::beginTransaction();
            $status = $this->taskChecklistRepo->toggleIsCompletedStatus($userAssignedChecklist);
            if($status){
                $this->changeTaskStatus($userAssignedChecklist);
            }
            DB::commit();
            return [
                    'checklist_id' => $userAssignedChecklist->id,
                    'is_completed' => $userAssignedChecklist->is_completed,
                    'name' => $userAssignedChecklist->name,
                ];
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function changeTaskStatus($userAssignedChecklist)
    {
        try{
            $completedStatus = 'completed';
            $notCompletedStatus = 'in_progress';
            $taskProgress = $userAssignedChecklist->task?->getTaskProgressInPercentage();
            $taskStatus = (intval($taskProgress) == 100) ? $completedStatus : $notCompletedStatus;
            return $this->taskRepo->changeTaskStatus($userAssignedChecklist->task,$taskStatus);
        }catch(Exception $e){
            throw $e;
        }

    }

    private function sendNotificationToTaskCheckListAssignedMember($title,$message,$userIds,$id)
    {
        SMPushHelper::sendProjectManagementNotification($title,$message,$userIds,$id);
    }
}

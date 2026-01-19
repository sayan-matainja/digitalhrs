<?php

namespace App\Repositories;

use App\Helpers\PMHelper;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskRepository
{
    public function getAllFilteredTasks($filterParameters,$select,$with): mixed
    {

        return Task::query()->select($select)->with($with)

            ->when(isset($filterParameters['task_id']), function ($query) use ($filterParameters) {
                $query->where('id', $filterParameters['task_id']);
            })
            ->when(isset($filterParameters['status']), function ($query) use ($filterParameters) {
                $query->where('status', $filterParameters['status']);
            })
            ->when(isset($filterParameters['priority']), function ($query) use ($filterParameters) {
                $query->where('priority', $filterParameters['priority']);
            })
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['assigned_member']), function ($query) use ($filterParameters) {
                $query->whereHas('assignedMembers.user',function($subQuery) use ($filterParameters){
                    $subQuery->whereIn('id', $filterParameters['assigned_member']);
                });
            })
            ->when(isset($filterParameters['project_id']), function ($query) use ($filterParameters) {
                $query->whereHas('project',function($subQuery) use ($filterParameters){
                    $subQuery->where('id', $filterParameters['project_id']);
                });
            })
            ->orderByRaw("
                CASE
                    WHEN status = 'in_progress' THEN 1
                    ELSE 2
                END ASC
            ")
            ->orderBy('end_date','desc')
            ->paginate( getRecordPerPage());
    }

    public function getAllTaskDetailForPieChart()
    {
        return Task::selectRaw('status, count(*) as count')
            ->where('is_active', 1)
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function store($validatedData):mixed
    {
        return Task::create($validatedData)->fresh();
    }

    public function toggleStatus($taskDetail):mixed
    {
        return $taskDetail->update([
            'is_active' => !$taskDetail->is_active,
        ]);
    }

    public function getAllTasks($select)
    {
        return Task::select($select)
            ->latest()->get();
    }

    public function findTaskDetailById($id,$with=[],$select=['*']):mixed
    {
        return Task::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function findAssignedMemberTaskDetailById($taskId,$with,$select):mixed
    {
        return Task::select($select)
            ->with($with)
            ->where(function($query) use ($taskId){
                $query->whereHas('assignedMembers',function($subQuery){
                    $subQuery->where('member_id', getAuthUserCode());
                })
                ->orWhereHas('project.projectLeaders', function ($query) {
                    $query->where('leader_id', getAuthUserCode());
                });
            })
            ->where('id',$taskId)
            ->first();
    }

    public function update($taskDetail, $validatedData)
    {
        if(isset($validatedData['attachments'])){
            $taskDetail->taskAttachments()->delete();
        }
        $taskDetail->assignedMembers()->delete();
        return$taskDetail->update($validatedData);
    }

    public function getUserAssignedTasksLists($userId,$select,$with,$perPage)
    {
        return Task::query()->select($select)->with($with)
            ->where(function($query) use ($userId){
                $query->whereHas('assignedMembers.user',function($subQuery) use ($userId){
                    $subQuery->where('id', $userId)
                        ->where('assignable_type','task');
                })
                ->orWhereHas('project.projectLeaders', function ($query)  use ($userId){
                    $query->where('leader_id', $userId);
                });
            })
            ->orderBy('end_date','desc')
            ->paginate($perPage);
    }

    public function delete(Task $taskDetail)
    {
        return $taskDetail->delete();
    }

    public function dropAssignedMembers($taskDetail)
    {
        $taskDetail->assignedMembers()->delete();
        return true;
    }

    public function assignMemberToTask(Task $taskDetail,$assignedMemberArray)
    {
        return $taskDetail->assignedMembers()->createMany($assignedMemberArray);
    }
    public function updateTaskMember(Task $taskDetail,$teamMemberArray)
    {
        $taskDetail->assignedMembers()->delete();
        return $taskDetail->assignedMembers()->createMany($teamMemberArray);
    }


    public function changeTaskStatus($taskDetail,$changedStatus)
    {
        return $taskDetail->update([
            'status' => $changedStatus
        ]);
    }

    public function toggleTaskByProjectId($projectId,$changedStatus)
    {
        return Task::where('project_id',$projectId)->update([
            'is_active' => $changedStatus
        ]);
    }

    public function getTasksByProject($projectId, $select = ['*'])
    {
        return Task::select($select)
            ->where('is_active', 1)
            ->where('project_id', $projectId)
            ->get();
    }

}

<?php

namespace App\Repositories;

use App\Models\TaskChecklist;
use Illuminate\Support\Facades\DB;

class TaskChecklistRepository
{

    public function store($validatedData):mixed
    {
        return TaskChecklist::create($validatedData)->fresh();
    }

    public function createManyChecklist($checklistsArrayData)
    {
       return TaskChecklist::insert($checklistsArrayData);
    }

    public function toggleIsCompletedStatus($checklistDetail):mixed
    {
        return $checklistDetail->update([
            'is_completed' => !$checklistDetail->is_completed,
        ]);
    }

    public function findTaskChecklistDetailById($id,$select,$with):mixed
    {
        return TaskChecklist::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }

    public function findTaskChecklistOfAssignedUserById($userId,$checklistId,$select,$with)
    {
        return TaskChecklist::select($select)
                            ->with($with)
                            ->where('id',$checklistId)
                            ->where('assigned_to',$userId)
                            ->first();
    }

    public function update($checklistDetail, $validatedData)
    {
        return $checklistDetail->update($validatedData);
    }

    public function delete(TaskChecklist $taskChecklistDetail)
    {
        return $taskChecklistDetail->delete();
    }



}

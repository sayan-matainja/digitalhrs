<?php

namespace App\Repositories;


use App\Models\TrainingType;

class TrainingTypeRepository
{

    public function getAllTrainingTypes($filterParameters,$select=['*'],$with=[])
    {
        return TrainingType::select($select)
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);

            })
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->where('title', 'like', '%' . $filterParameters['type'] . '%');
            })
            ->withCount($with)->get();
    }

    public function getAllActiveTrainingTypes($select=['*'])
    {
        return TrainingType::select($select)->where('status',1)->get();
    }

    public function getBranchTrainingTypes($branchId,$select=['*'])
    {
        return TrainingType::select($select)->where('branch_id',$branchId)->where('status',1)->get();
    }

    public function find($id,$select=['*'],$with=[])
    {
        return TrainingType::select($select)->withCount($with)->where('id',$id)->first();
    }

    public function create($validatedData)
    {
        return TrainingType::create($validatedData)->fresh();
    }

    public function update($trainingTypeDetail,$validatedData)
    {
        return $trainingTypeDetail->update($validatedData);
    }

    public function delete($trainingTypeDetail)
    {
        return $trainingTypeDetail->delete();
    }

    public function toggleStatus($trainingTypeDetail)
    {
        return $trainingTypeDetail->update([
            'status' => !$trainingTypeDetail->status,
        ]);
    }
}

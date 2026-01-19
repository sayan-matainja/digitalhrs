<?php

namespace App\Repositories;



use App\Models\TerminationType;

class TerminationTypeRepository
{

    public function getAllTerminationTypes($filterParameters,$select=['*'],$with=[])
    {
        return TerminationType::select($select)->withCount($with)
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->where('title', 'like', '%' . $filterParameters['type'] . '%');
            })
            ->get();
    }

    public function getAllActiveTerminationTypes($select=['*'])
    {
        return TerminationType::select($select)->where('status',1)->get();
    }
    public function getBranchTerminationTypes($branchId, $select=['*'])
    {
        return TerminationType::select($select)->where('status',1)->where('branch_id',$branchId)->get();
    }

    public function find($id,$select=['*'],$with=[])
    {
        return TerminationType::select($select)->withCount($with)->where('id',$id)->first();
    }

    public function create($validatedData)
    {
        return TerminationType::create($validatedData)->fresh();
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

<?php

namespace App\Repositories;

use App\Models\AwardType;

class AwardTypeRepository
{

    public function getAllAwardTypes($filterParameters,$select = ['*'], $with = [])
    {
        return AwardType::select($select)->withCount($with)
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['type']), function($query) use ($filterParameters){
                $query->where('title', 'like', '%' . $filterParameters['type'] . '%');
            })
            ->get();
    }

    public function getAllActiveAwardTypes($select = ['*'])
    {
        return AwardType::select($select)->where('status', 1)->get();
    }

    public function getBranchAwardTypes($branchId, $select = ['*'])
    {
        return AwardType::select($select)->where('status', 1)->where('branch_id', $branchId)->get();
    }

    public function findAwardTypeById($id, $select = ['*'], $with = [])
    {
        return AwardType::with($with)->select($select)->where('id', $id)->first();
    }

    public function create($validatedData)
    {
        return AwardType::create($validatedData)->fresh();
    }

    public function update($awardTypeDetail, $validatedData)
    {
        return $awardTypeDetail->update($validatedData);
    }

    public function delete($awardTypeDetail)
    {
        return $awardTypeDetail->delete();
    }

    public function toggleStatus($awardTypeDetail)
    {
        return $awardTypeDetail->update([
            'status' => !$awardTypeDetail->status,
        ]);
    }
}

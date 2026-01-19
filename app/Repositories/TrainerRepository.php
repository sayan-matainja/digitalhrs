<?php

namespace App\Repositories;


use App\Helpers\AppHelper;
use App\Models\Trainer;
use Illuminate\Support\Facades\DB;

class TrainerRepository
{
    public function getAllTrainerPaginated($filterParameters, $select = ['*'], $with = [])
    {
        return Trainer::select($select)->with($with)
            ->when(isset($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->where('branch_id', $filterParameters['branch_id']);
            })
            ->when(isset($filterParameters['trainer_type']), function ($query) use ($filterParameters) {
                $query->where('trainer_type', $filterParameters['trainer_type']);
            })
            ->when(isset($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->where('department_id', $filterParameters['department_id']);
            })
            ->when(isset($filterParameters['employee_id']), function ($query) use ($filterParameters) {
                $query->where('employee_id', $filterParameters['employee_id']);
            })
            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('name', 'like', '%'.$filterParameters['name'].'%');
            })
            ->paginate( getRecordPerPage());
    }

    public function find($id, $select = ['*'], $with = [])
    {
        return Trainer::select($select)
            ->with($with)
            ->where('id', $id)
            ->first();
    }

    public function findTrainers($ids, $select = ['*'])
    {
        return Trainer::select($select)
            ->whereIn('id', $ids)
            ->get();
    }

    public function findByType($type)
    {
        return Trainer::select(
            'trainers.id',
            DB::raw('COALESCE(trainers.name, users.name) AS name')
        )
            ->leftJoin('users', 'trainers.employee_id', 'users.id')
            ->where('trainers.trainer_type', $type)
            ->get();
    }

    public function store($validatedData)
    {
        $validatedData['created_by'] = auth()->user()->id ?? null;
        return Trainer::create($validatedData)->fresh();
    }

    public function update($trainerDetail, $validatedData)
    {
        return $trainerDetail->update($validatedData);
    }

    public function delete($trainerDetail)
    {
        return $trainerDetail->delete();
    }

    public function toggleStatus($trainerDetail)
    {
        return $trainerDetail->update([
            'status' => !$trainerDetail->status,
        ]);
    }


}

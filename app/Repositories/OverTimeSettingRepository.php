<?php

namespace App\Repositories;

use App\Models\OverTimeSetting;

class OverTimeSettingRepository
{

    public function getAll($select=['*']):mixed
    {
        return OverTimeSetting::select($select)
            ->withCount('otEmployees')
            ->latest()
            ->get();
    }


    public function find($id, $with=[]):mixed
    {
        return OverTimeSetting::with($with)
            ->where('id',$id)
            ->first();

    }

    public function save($validatedData)
    {

        return OverTimeSetting::create($validatedData);
    }

    public function update($otData,$validatedData)
    {
        return $otData->update($validatedData);
    }

    public function delete($overtimeData)
    {
        $overtimeData->otEmployees()->delete();
        $overtimeData->otDepartments()->delete();
        return $overtimeData->delete();
    }

    public function toggleIsActiveStatus($overTimeDetail)
    {
        return $overTimeDetail->update([
            'is_active' => !$overTimeDetail->is_active
        ]);
    }
    public function saveEmployee(OverTimeSetting $otDetail,$userArray)
    {
        return $otDetail->otEmployees()->createMany($userArray);
    }

    public function updateEmployee(OverTimeSetting $otDetail,$userArray)
    {
        $otDetail->otEmployees()->delete();
        return $otDetail->otEmployees()->createMany($userArray);
    }
    public function saveDepartment(OverTimeSetting $otDetail,$departmentArray)
    {
        return $otDetail->otDepartments()->createMany($departmentArray);
    }

    public function updateDepartment(OverTimeSetting $otDetail,$departmentArray)
    {
        $otDetail->otDepartments()->delete();
        return $otDetail->otDepartments()->createMany($departmentArray);
    }
}

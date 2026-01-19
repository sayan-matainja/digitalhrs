<?php

namespace App\Repositories;

use App\Models\Bonus;
use App\Models\User;

class BonusRepository
{
    public function getAll($select=['*'],$with=[])
    {
        return Bonus::with($with)->select($select)->get();
    }

    public function find($id,$select=['*'],$with=[])
    {
        return Bonus::with($with)->select($select)
            ->where('id',$id)
            ->first();
    }

    public function findByMonth($month, $select=['*'])
    {
        return Bonus::select($select)
            ->where('applicable_month',$month)
            ->where('is_active',1)
            ->first();
    }

    public function store($validatedData)
    {
        return Bonus::create($validatedData)->fresh();
    }

    public function toggleStatus($bonusDetail)
    {
        return $bonusDetail->update([
            'is_active' => !$bonusDetail->is_active
        ]);
    }

    public function update($bonusDetail, $validatedData)
    {
         $bonusDetail->update($validatedData);
         return $bonusDetail->fresh();
    }


    public function delete($bonusDetail)
    {
        $bonusDetail->bonusEmployee()->delete();
        $bonusDetail->bonusDepartment()->delete();
        return $bonusDetail->delete();
    }

    public function pluckAllBonusLists()
    {
        return Bonus::where('is_active',1)
            ->get();
    }

    public function findByEmployeeAndMonth($employeeId,$month)
    {
        $employee = User::where('id',$employeeId)->first();
        $departmentId = $employee->department_id;
        $branchId = $employee->branch_id;
        return Bonus::where('applicable_month', $month)
            ->where('is_active', 1)
            ->where(function ($query) use ($employeeId, $departmentId, $branchId) {
                $query->whereHas('bonusEmployee', function ($q) use ($employeeId) {
                    $q->where('employee_id', $employeeId);
                });

                $query->orWhere(function ($q) use ($departmentId, $branchId) {
                    $q->where('apply_for_all', 1)
                        ->where(function ($sub) use ($departmentId, $branchId) {
                            $sub->whereHas('bonusDepartment', function ($d) use ($departmentId) {
                                $d->where('department_id', $departmentId);
                            })
                                ->orWhere('branch_id', $branchId);
                        });
                });
            })
            ->first();
    }
    public function saveEmployee(Bonus $bonusDetail,$userArray)
    {
        return $bonusDetail->bonusEmployee()->createMany($userArray);
    }

    public function updateEmployee($bonusDetail,$userArray)
    {
        $bonusDetail->bonusEmployee()->delete();
        return $bonusDetail->bonusEmployee()->createMany($userArray);
    }
    public function saveDepartment(Bonus $bonusDetail,$departmentArray)
    {
        return $bonusDetail->bonusDepartment()->createMany($departmentArray);
    }

    public function updateDepartment($bonusDetail,$departmentArray)
    {
        $bonusDetail->bonusDepartment()->delete();
        return $bonusDetail->bonusDepartment()->createMany($departmentArray);
    }

}

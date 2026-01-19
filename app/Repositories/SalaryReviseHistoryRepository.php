<?php

namespace App\Repositories;

use App\Models\SalaryReviseHistory;
use Illuminate\Database\Eloquent\Collection;

class SalaryReviseHistoryRepository
{

    public function getAllEmployeeSalaryHistoryList($employeeId,$select=['*'],$with=[]): Collection|array
    {
        return SalaryReviseHistory::query()
            ->with($with)
            ->select($select)
            ->where('employee_id',$employeeId)
            ->orderByDesc('id')
            ->get();
    }

    public function getSalaryHistoryByEmployee($employeeId)
    {
        return SalaryReviseHistory::where('employee_id',$employeeId)
            ->orderBy('id')
            ->first();
    }

    public function store($validatedData)
    {
        return SalaryReviseHistory::create($validatedData)->fresh();
    }

}

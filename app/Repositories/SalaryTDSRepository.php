<?php

namespace App\Repositories;

use App\Models\SalaryTDS;

class SalaryTDSRepository
{
    public function getAllSalaryTDSDetail($select=['*'])
    {
        return SalaryTDS::select($select)
            ->get();
    }

    public function getSalaryTdSDetailByMaritalStatus($maritalStatus,$select=['*'])
    {
        return SalaryTDS::select($select)->where('marital_status',$maritalStatus)->get();
    }

    public function findSalaryTDSDetailById($id,$select=['*'])
    {
        return SalaryTDS::select($select)->where('id',$id)->first();
    }


    public function store($validatedData)
    {
        return SalaryTDS::insert($validatedData);
    }

    public function update($salaryTDSDetail, $validatedData)
    {
        $salaryTDSDetail->update($validatedData);
        return $salaryTDSDetail->fresh();
    }

    public function toggleSalaryTDSDetail($salaryTDSDetail)
    {
        return $salaryTDSDetail->update([
           'status' => !$salaryTDSDetail->status,
        ]);
    }

    public function delete($salaryTDSDetail)
    {
        return $salaryTDSDetail->delete();
    }

}

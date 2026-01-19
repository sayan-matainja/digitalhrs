<?php

namespace App\Repositories;

use App\Models\NfcAttendance;

class NFCRepository
{

    /**
     * @param array $select
     * @return mixed
     */
    public function getAll($filterData): mixed
    {

        return NfcAttendance:: query()
            ->when(isset($filterData['branch_id']), function ($query) use ($filterData) {
                $query->whereHas('createdBy',function ($q) use ($filterData){
                    $q->where('branch_id', $filterData['branch_id']);
                });
            })
            ->when(isset($filterData['department_id']), function ($query) use ($filterData) {
                $query->whereHas('createdBy',function ($q) use ($filterData){
                    $q->where('department_id', $filterData['department_id']);
                });
            })
            ->when(isset($filterData['employee_id']), function ($query) use ($filterData) {
                $query->where('created_by', $filterData['employee_id']);
            })
            ->get();

    }

    public function verifyNfc($identifier = ''): mixed
    {

        $nfcData = NfcAttendance::query();
        return $nfcData->where('identifier', $identifier)->first();

    }

    /**
     * @param $validatedData
     * @return mixed
     */
    public function store($validatedData): mixed
    {
        return NfcAttendance::create($validatedData)->fresh();
    }


    /**
     * @param $id
     * @return mixed
     */
    public function findNFCDetailById($id): mixed
    {
        return NfcAttendance::find($id);
    }


    public function delete($nfcDetail): ?bool
    {
        return $nfcDetail->delete();
    }

}

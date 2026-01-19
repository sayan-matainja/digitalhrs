<?php

namespace App\Repositories;


use App\Models\QrAttendance;

class QRCodeRepository
{

    /**
     * @param array $select
     * @return mixed
     */
    public function getByIdentifier($identifier): mixed
    {

        return QrAttendance::where('identifier',$identifier)->first();
    }
    public function getAll($filterData): mixed
    {

        return QrAttendance::with(['branch:id,name'])
            ->when(isset($filterData['branch_id']), function($query) use ($filterData) {
                $query->where('branch_id', $filterData['branch_id']);

            })
            ->when(isset($filterData['department_id']), function($query) use ($filterData) {
                $query->where('department_id', $filterData['department_id']);
            })
            ->get();
    }

    /**
     * @param $validatedData
     * @return mixed
     */
    public function store($validatedData):mixed
    {
        return QrAttendance::create($validatedData)->fresh();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findQr($id):mixed
    {
        return QrAttendance::find($id);
    }

    public function update($qrDetail, $validatedData)
    {
        return $qrDetail->update($validatedData);
    }

    public function delete($qrDetail): ?bool
    {
        return $qrDetail->delete();
    }

}

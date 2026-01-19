<?php

namespace App\Repositories;


use App\Models\Device;

class DeviceRepository
{

    public function getAllDevicesPaginated($select=['*'],$with=[])
    {
        return Device::select($select)->with($with)

//             ->when(isset($filterParameters['branch_id']), function($query) use ($filterParameters){
//                $query->where('branch_id', $filterParameters['branch_id']);
//            })
//            ->when(isset($filterParameters['name']), function ($query) use ($filterParameters) {
//                $query->where('name', 'like', '%' . $filterParameters['name'] . '%');
//            })
            ->latest()
            ->paginate( getRecordPerPage());
    }



    public function find($id,$select=['*'],$with=[])
    {
        return Device::select($select)
            ->with($with)
            ->where('id',$id)
            ->first();
    }
    public function findBySerialNumber($serialNumber)
    {
        return Device::where('serial_number',$serialNumber)
            ->first();
    }

    public function store($validatedData)
    {
        return Device::create($validatedData)->fresh();
    }

    public function update($deviceDetail,$validatedData)
    {
        return $deviceDetail->update($validatedData);
    }

    public function delete($deviceDetail)
    {
        return $deviceDetail->delete();
    }



}

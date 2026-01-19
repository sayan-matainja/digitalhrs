<?php

namespace App\Repositories;


use App\Models\UnderTimeSetting;

class UnderTimeSettingRepository
{

    public function getAll($select=['*'], $first=''):mixed
    {
        $utData = UnderTimeSetting::select($select);
        if(!empty($first)){
            $utData = $utData->first();
        }else{
            $utData =  $utData->get();
        }
        return $utData;
    }


    public function find($id):mixed
    {
        return UnderTimeSetting::findOrFail($id);
    }

    public function save($validatedData)
    {

        return UnderTimeSetting::create($validatedData);
    }

    public function update($utData,$validatedData)
    {
        return $utData->update($validatedData);
    }

    public function delete($underTimeData)
    {
        return $underTimeData->delete();
    }

    public function toggleIsActiveStatus($underTimeDetail)
    {
        return $underTimeDetail->update([
            'is_active' => !$underTimeDetail->is_active
        ]);
    }
}

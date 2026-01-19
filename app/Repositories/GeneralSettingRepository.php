<?php

namespace App\Repositories;

use App\Models\GeneralSetting;

class GeneralSettingRepository
{
    public function getAllGeneralSettingDetails($select = ['*'])
    {
        $keys = ['attendance_method','attendance_limit','advance_salary_limit','firebase_key','loan_id_prefix','loan_limit'];
        return GeneralSetting::select($select)->whereNotIn('key',$keys)->get();
    }

    public function getGeneralAttendanceSettingDetails($select = ['*'])
    {
        $keys = ['attendance_method','attendance_limit'];
        return GeneralSetting::select($select)->whereIn('key',$keys)->get();
    }

    public function findOrFailGeneralSettingDetailById($id,$select=['*'])
    {
        return GeneralSetting::select($select)->where('id',$id)->firstOrFail();
    }

    public function getGeneralSettingByType($type,$select=['*'])
    {
        return GeneralSetting::select($select)->where('type',$type)->get();
    }

    public function getGeneralSettingByKey($key,$select=['*'])
    {
        return GeneralSetting::select($select)->where('key',$key)->firstOrFail();
    }

    public function getLoanSetting($select=['*'])
    {
        $keys = ['loan_id_prefix','loan_limit'];
        return GeneralSetting::select($select)->whereIn('key',$keys)->get();
    }


    public function store($validatedData)
    {
        return GeneralSetting::create($validatedData)->fresh();
    }

    public function update($generalSettingDetail,$validatedData)
    {
        return $generalSettingDetail->update($validatedData);
    }

    public function delete($id)
    {
        $generalSettingDetail = $this->findOrFailGeneralSettingDetailById($id);
        return $generalSettingDetail->delete();
    }

}


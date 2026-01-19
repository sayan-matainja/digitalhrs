<?php

namespace App\Repositories;


use App\Models\AttendanceSetting;

class AttendanceSettingRepository
{
    public function getAll($select = ['*'])
    {
        return AttendanceSetting::select($select)->get();
    }

    public function findById($id,$select=['*'])
    {
        return AttendanceSetting::select($select)->where('id',$id)->firstOrFail();
    }

    public function update($settingDetail,$validatedData)
    {
        return $settingDetail->update($validatedData);
    }

    public function toggleStatus($id)
    {
        $attendanceSettings = $this->findById($id);
        return $attendanceSettings->update([
            'status' => !$attendanceSettings->status,
        ]);
    }



}


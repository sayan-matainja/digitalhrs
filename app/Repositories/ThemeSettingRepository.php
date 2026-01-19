<?php

namespace App\Repositories;


use App\Models\ThemeSetting;

class ThemeSettingRepository
{
    public function getAll($select = ['*'])
    {

        return ThemeSetting::select($select)->first();

    }

    public function find($id,$select=['*'])
    {
        return ThemeSetting::select($select)->where('id',$id)->firstOrFail();
    }

    public function findByKey($key,$select=['*'])
    {
        return ThemeSetting::select($select)->where('key',$key)->firstOrFail();
    }

    public function store($validatedData)
    {
        return ThemeSetting::create($validatedData)->fresh();
    }

    public function update($themeSettingDetail,$validatedData)
    {
        return $themeSettingDetail->update($validatedData);
    }

    public function delete($id)
    {
        $themeSettingDetail = $this->find($id);
        return $themeSettingDetail->delete();
    }

}


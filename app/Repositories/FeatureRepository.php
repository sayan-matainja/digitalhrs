<?php

namespace App\Repositories;

use App\Models\AppSetting;
use App\Models\Feature;

class FeatureRepository
{

    public function getAllFeatures($select=['*'])
    {
        return Feature::select($select)->orderBy('group','desc')->orderBy('name')->get();
    }
    public function getAll($select=['*'])
    {
        return Feature::select($select)->where('key','!=','nfc-qr')->orderBy('group','desc')->orderBy('name')->get();
    }

    public function findFeatureById($id,$select=['*'])
    {
        return Feature::select($select)->where('id',$id)->first();
    }

    public function findFeatureByKey($key)
    {
        return Feature::where('key',$key)->first();
    }

    public function toggleStatus($id)
    {
        $appSettings = $this->findFeatureById($id);
        return $appSettings->update([
            'status' => !$appSettings->status,
        ]);
    }
}

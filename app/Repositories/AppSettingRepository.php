<?php

namespace App\Repositories;

use App\Models\AppSetting;

class AppSettingRepository
{

    public function getAllAppSettings($select = ['*'])
    {
        $nepaliDate = config('app.nepali_date', false);
        $nepaliDateSlug = 'bs';
        $leaveCountResetSlug = 'reset-leave-count';

        return AppSetting::select($select)
            ->when(!$nepaliDate, function ($query) use ($nepaliDateSlug,$leaveCountResetSlug) {
                $query->where('slug', '!=', $nepaliDateSlug)->where('slug', '!=', $leaveCountResetSlug);
            })
            ->where('slug','!=','attendance-note')
            ->latest()
            ->get();
    }

    public function getAttendanceAppSettings($select = ['*'])
    {
        $slugs = ['attendance-note'];
        return AppSetting::select($select)
            ->whereIn('slug', $slugs)
            ->get();
    }

    public function findAppSettingDetailById($id,$select=['*'])
    {
        return AppSetting::select($select)->where('id',$id)->first();
    }

    public function findAppSettingDetailBySlug($slug)
    {
        return AppSetting::where('slug',$slug)->first();
    }

    public function toggleStatus($id)
    {
        $appSettings = $this->findAppSettingDetailById($id);
        return $appSettings->update([
            'status' => !$appSettings->status,
        ]);
    }

    public function toggleTheme($themeDetail)
    {
        return $themeDetail->update([
            'status' => !$themeDetail->status,
        ]);
    }
}

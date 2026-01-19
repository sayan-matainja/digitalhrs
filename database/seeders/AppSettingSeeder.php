<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $appSetting = [
            [
                'name'=> 'authorize login',
                'slug' => Str::slug('authorize login'),
                'status' => 0
            ],

            [
                'name'=> 'override bssid',
                'slug' => Str::slug('override bssid'),
                'status' => 0
            ],

            [
                'name'=> '24 hour format',
                'slug' => Str::slug('24 hour format'),
                'status' => 0
            ],

            [
                'name'=> 'Date In BS',
                'slug' => Str::slug('BS'),
                'status' => 0
            ],

            [
                'name'=> 'Reset Leave Count on 1st Shrawan',
                'slug' => Str::slug('Reset Leave Count'),
                'status' => 0
            ],
            [
                'name'=> 'Enable Employee Location',
                'slug' => Str::slug('Enable Employee Location'),
                'status' => 0
            ]
        ];

        $appSettingSlugs = array_column($appSetting, 'slug');

        $existingKeys = DB::table('app_settings')->pluck('slug')->toArray();

        $slugsToDelete = array_diff($existingKeys, $appSettingSlugs);

        if (!empty($slugsToDelete)) {
            DB::table('app_settings')->whereIn('slug', $slugsToDelete)->delete();
        }
        $newSettings = array_filter($appSetting, function ($setting) use ($existingKeys) {
            return !in_array($setting['slug'], $existingKeys);
        });

        if (!empty($newSettings)) {
            DB::table('app_settings')->insert($newSettings);
        }
    }
}

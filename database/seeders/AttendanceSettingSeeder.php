<?php

namespace Database\Seeders;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendanceSetting = [

            [
                'name'=> 'Attendance Note',
                'slug' => 'attendance_note',
                'value'=>null,
                'values'=>null,
                'status' => 0
            ],
            [
                'name' => 'Attendance Limit',
                'slug' => 'attendance_limit',
                'value' => 1,
                'values'=>null,
                'status' => 1,
            ],

            [
                'name' => 'Attendance Method',
                'slug' => 'attendance_method',
                'value'=>null,
                'values' => json_encode(['default']),
                'status' => 1,
//                'description' => 'Note: for wifi=> This setting will not affect field type users. Those type of users will still be able to perform check in checkout via mobile app.'
            ],
        ];


        $existingKeys = DB::table('attendance_settings')->pluck('slug')->toArray();


        $newSettings = array_filter($attendanceSetting, function ($setting) use ($existingKeys) {
            return !in_array($setting['slug'], $existingKeys);
        });

        if (!empty($newSettings)) {
            DB::table('attendance_settings')->insert($newSettings);
        }

    }

}

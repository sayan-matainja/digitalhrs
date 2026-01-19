<?php

namespace Database\Seeders;

use App\Enum\EmployeeAttendanceTypeEnum;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $generalSetting = [
            [
                'name' => 'Set Number Of Days for local Push Notification',
                'type' => 'configuration',
                'key' => 'attendance_notify',
                'value' => "7",
                'description' => 'Setting no of days will automatically send the data of those days to the mobile application.Receiving this data on the mobile end will allow the mobile application to set local push notification for those dates. The local push notification will help employees remember to check in on time as well as to check out when the shift is about to end.'
            ],
            [
                'name' => 'Advance Salary Limit(%)',
                'type' => 'general',
                'key' => 'advance_salary_limit',
                'value' => "50",
                'description' => 'Set the maximum amount in percent a employee can request in advance based on gross salary.'
            ],
            [
                'name' => 'Employee Code Prefix',
                'type' => 'general',
                'key' => 'employee_code_prefix',
                'value' => "EMP",
                'description' => 'This prefix will be used to make employee code.'
            ],
            [
                'name' => 'Award Display Limit',
                'type' => 'general',
                'key' => 'award_display_limit',
                'value' => "14",
                'description' => 'award display limit in mobile app.'
            ],
            [
                'name' => 'Records Per Page',
                'type' => 'general',
                'key' => 'records_per_page',
                'value' => "15",
                'description' => 'Display the number of records in list page.'
            ],
            [
                'name' => 'Bonus Applicable After',
                'type' => 'general',
                'key' => 'bonus_applied_after',
                'value' => "12",
                'description' => 'After how many months bonus is applicable to employees.'
            ],

            [
                'name' => 'Loan Id Prefix',
                'type' => 'loan',
                'key' => 'loan_id_prefix',
                'value' => "Loan",
                'description' => 'This prefix will be used to make loan id.'
            ],
            [
                'name' => 'Loan Multiplier Limit',
                'type' => 'loan',
                'key' => 'loan_limit',
                'value' => 2,
                'description' => 'The maximum multiple of basic salary, which employee can request for a single loan application (e.g., 2 means 2 times basic salary).'
            ]
        ];


        $existingKeys = DB::table('general_settings')->pluck('key')->toArray();


        $newSettings = array_filter($generalSetting, function ($setting) use ($existingKeys) {
            return !in_array($setting['key'], $existingKeys);
        });

        if (!empty($newSettings)) {
            DB::table('general_settings')->insert($newSettings);
        }

    }

}

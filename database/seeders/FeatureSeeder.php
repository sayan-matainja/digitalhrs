<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /** Do not change key for all features. if keys are changed need to update api. */
        $features = [

            [
                'group' => 'Office Desk',
                'name' => 'Project Management',
                'key' => Str::slug('Project Management'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Meeting',
                'key' => Str::slug('Meeting'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            [
                'group' => 'Finance',
                'name' => 'TADA',
                'key' => Str::slug('TADA'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

            [
                'group' => 'Finance',
                'name' => 'Payroll Management',
                'key' => Str::slug('Payroll Management'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Finance',
                'name' => 'Advance Salary',
                'key' => Str::slug('Advance Salary'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Additional',
                'name' => 'Support',
                'key' => Str::slug('Support'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Additional',
                'name' => 'Dark Mode',
                'key' => Str::slug('Dark Mode'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Attendance',
                'name' => 'NFC & QR',
                'key' => Str::slug('NFC & QR'),
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Training',
                'key' => Str::slug('Training'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Award',
                'key' => Str::slug('award'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Finance',
                'name' => 'Loan',
                'key' => Str::slug('Loan'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Event',
                'key' => Str::slug('Event'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Complaint',
                'key' => Str::slug('Complaint'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Warning',
                'key' => Str::slug('Warning'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Resignation',
                'key' => Str::slug('Resignation'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Office Desk',
                'name' => 'Assets',
                'key' => Str::slug('assets'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'group' => 'Finance',
                'name' => 'Loan',
                'key' => Str::slug('Loan'),
                'status' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],

        ];


        $featureKeys = array_column($features, 'key');

        $existingKeys = DB::table('features')->pluck('key')->toArray();

        $keysToDelete = array_diff($existingKeys, $featureKeys);

        if (!empty($keysToDelete)) {
            DB::table('features')->whereIn('key', $keysToDelete)->delete();
        }

        $newFeatures = array_filter($features, function ($feature) use ($existingKeys) {
            return !in_array($feature['key'], $existingKeys);
        });

        if (!empty($newFeatures)) {
            DB::table('features')->insert($newFeatures);
        }

    }
}

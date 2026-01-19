<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('leave_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $leaveTypes = [
            [
                'name' => 'sick leave',
                'slug' => 'sick-leave',
                'leave_allocated' => 10,
                'company_id' => 1,
                'is_active' => 1,
                'early_exit' => 0,
                'created_at' => Carbon::now(),
                'updated_at' =>  Carbon::now(),
            ],

            [
                'name' => 'paid leave',
                'slug' => 'paid-leave',
                'leave_allocated' => 6,
                'company_id' => 1,
                'is_active' => 1,
                'early_exit' => 0,
                'created_at' => Carbon::now(),
                'updated_at' =>  Carbon::now(),
            ],

            [
                'name' => 'urgent leave',
                'slug' => 'urgent-leave',
                'leave_allocated' => 0,
                'company_id' => 1,
                'is_active' => 1,
                'early_exit' => 0,
                'created_at' => Carbon::now(),
                'updated_at' =>  Carbon::now(),
            ],

            [
                'name' => 'unpaid leave',
                'slug' => 'unpaid-leave',
                'leave_allocated' => 0,
                'company_id' => 1,
                'is_active' => 1,
                'early_exit' => 0,
                'created_at' => Carbon::now(),
                'updated_at' =>  Carbon::now(),
            ],

        ];

        DB::table('leave_types')->insert($leaveTypes);

    }
}

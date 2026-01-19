<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $to = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', '2015-5-5 3:30:34');
//        $from = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', '2015-5-6 9:30:34');
//        $diff_in_days = ($to->diffInDays($from) + 1);

        $start_date = Carbon::create('2023-1-1 3:30:34');
        $end_date = Carbon::now();

        $different_days = $start_date->diffInDays($end_date);


//        $currentTime = strtotime(Carbon::now()->format('h:i:s'));
//        $checkTime = strtotime('10:53:21');
//        $diff = $checkTime - $currentTime;

    }
}

<?php

namespace Database\Seeders;

use App\Models\FiscalYear;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SSFSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $fiscalYear = FiscalYear::where('is_running',1)->first();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('ssf')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $ssfData = [
            [
                'office_contribution' => 20,
                'employee_contribution' => 11,
                'is_active' => 1,
                'applicable_date' => $fiscalYear?->start_date,
                'enable_tax_exemption' => 0,
                'created_at' => Carbon::now(),
                'updated_at' =>  Carbon::now(),
            ],

        ];

        DB::table('ssf')->insert($ssfData);



        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pf')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $pfData = [
            [
                'office_contribution' => 10,
                'employee_contribution' => 10,
                'is_active' => 1,
                'applicable_date' => $fiscalYear?->start_date,
                'created_at' => Carbon::now(),
                'updated_at' =>  Carbon::now(),
            ],

        ];

        DB::table('pf')->insert($pfData);
    }
}

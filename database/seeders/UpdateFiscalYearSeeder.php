<?php

namespace Database\Seeders;

use App\Models\FiscalYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateFiscalYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $todayDate = now()->format('Y-m-d');
        $fiscalYearData = FiscalYear::query()
            ->where('start_date','<=', $todayDate)
            ->where('end_date','>=', $todayDate)
            ->first();
        if (!empty($fiscalYearData)) {
            DB::transaction(function () use ($fiscalYearData) {
                $fiscalYearData->update(['is_running' => 1]);
                FiscalYear::query()
                    ->where('id', '!=', $fiscalYearData->id)
                    ->update(['is_running' => 0]);
            });
        }

    }
}

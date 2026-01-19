<?php

namespace App\Console\Commands;

use App\Enum\ResignationStatusEnum;
use App\Helpers\AppHelper;
use App\Models\FiscalYear;
use App\Models\Resignation;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Console\Command;

class CreateFiscalYear extends Command
{
    const IS_ACTIVE = 1;

    protected $signature = 'command:create-fiscal-year';

    protected $description = 'create fiscal year and update current status of old fiscal years.';

    public function handle()
    {

        $isBsEnabled = AppHelper::ifDateInBsEnabled();

        $fiscalYearDetail = FiscalYear::orderBy('start_date','desc')->first();



        if (!empty($fiscalYearDetail)) {

            $lastDay = $fiscalYearDetail->end_date;
            $nextDay = date('Y-m-d', strtotime($lastDay . ' +1 day'));
            if(strtotime(date('Y-m-d')) == strtotime($nextDay)){
                if($isBsEnabled) {

                    $currentBsYear = (int) explode('/', $fiscalYearDetail->year)[1];
                    $newBsYearStart = $currentBsYear;
                    $newBsYearEnd = $currentBsYear + 1;
                    $newYear = "$newBsYearStart/$newBsYearEnd";


                    $newStartDate = $nextDay;
                    $startFirst = $newBsYearEnd.'-04-01';

                    $englishDate = AppHelper::nepToEngDateInYmdFormat($startFirst);

                    $newEndDate =  date('Y-m-d', strtotime($englishDate . ' -1 day'));; // e.g., 2083-03-31 BS (~2026-07-15)

                } else {
                    // Gregorian calendar logic
                    $currentYear = (int) date('Y', strtotime($nextDay));
                    $newYear = (string) $currentYear;
                    $newStartDate = "$currentYear-01-01";
                    $newEndDate = "$currentYear-12-31";
                }
                FiscalYear::query()->update(['is_running' => 0]);

                // Create the new fiscal year
                FiscalYear::create([
                    'year' => $newYear,
                    'start_date' => $newStartDate,
                    'end_date' => $newEndDate,
                    'is_running' => 1,
                ]);
            }
        }
        $this->info('Fiscal Year Created and old data updated!');
    }
}

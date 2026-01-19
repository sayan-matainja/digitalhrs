<?php

namespace App\Imports;

use App\Helpers\AppHelper;
use App\Models\Holiday;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class HolidaysImport implements ToModel,WithHeadingRow
{
    use Importable;

    const IS_ACTIVE = 1;

    /**
     * @throws \Exception
     */
    public function model(array $row): Holiday
    {

        Log::info(json_encode($row));
       $companyId =  AppHelper::getAuthUserCompanyId();
       $eventDate = date('Y-m-d', strtotime($row['event_date']));

       $existingHoliday = Holiday::where('event_date',$eventDate)->first();
       if($existingHoliday){
          Holiday::destroy($existingHoliday->id);
       }
        return new Holiday([
            "event" => $row['event'],
            "event_date" => $eventDate,
            "note" => $row['note'],
            "is_active" => self::IS_ACTIVE,
            "company_id" => $companyId,
            "is_public_holiday" => $row['is_public_holiday']
        ]);


    }
}

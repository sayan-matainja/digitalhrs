<?php

namespace App\Resources\Attendance;


use App\Helpers\AttendanceHelper;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NightAttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        $time = $this->night_checkout ?? \Carbon\Carbon::now();

        $checkInTime = Carbon::parse($this->night_checkin);
        $checkOutTime = Carbon::parse($time);


        $productiveTimeInMin = $checkOutTime->diffInMinutes($checkInTime);

        return [
            'check_in_at' => isset($this->night_checkin) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($this->night_checkin) : '-',
            'check_out_at' => isset($this->night_checkout) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($this->night_checkout) : '-',
            'productive_time_in_min' => $productiveTimeInMin
        ];
    }
}













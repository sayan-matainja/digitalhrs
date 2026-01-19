<?php

namespace App\Resources\Dashboard;


use App\Helpers\AttendanceHelper;
use App\Resources\Attendance\TodayAttendanceResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class EmployeeTodayAttendance extends JsonResource
{
    public function toArray($request)
    {
        $attendance = $this->employeeTodayAttendance->first();



        $checkInAt = isset($attendance->check_in_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->check_in_at) : (isset($attendance->night_checkin) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($attendance->night_checkin) : '-');

        if(isset($attendance->check_in_at)){
            $productiveTimeInMin = $this->calculateProductiveTime($attendance->check_in_at, $attendance->check_out_at);
        }else{
            if(isset($attendance->night_checkin)){
                $productiveTimeInMin = $this->calculateProductiveTime($attendance->night_checkin, $attendance->night_checkout);
            }else{
                $productiveTimeInMin = 0;
            }
        }
        $checkOutAt = isset($attendance->check_out_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendance->check_out_at) : (isset($attendance->night_checkout) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($attendance->night_checkout) : '-') ;


        return [
            'check_in_at' => $checkInAt,
            'check_out_at' => $checkOutAt,
            'productive_time_in_min' => $productiveTimeInMin,
        ];
    }

    private function calculateProductiveTime($checkInAt, $checkOutAt)
    {
        if (!$checkInAt) {
            return 0;
        }

        $endTime = $checkOutAt ? Carbon::parse($checkOutAt) : Carbon::now();
        return Carbon::parse($checkInAt)->diffInMinutes($endTime);
    }


}












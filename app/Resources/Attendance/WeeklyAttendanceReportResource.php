<?php

namespace App\Resources\Attendance;


use App\Helpers\AttendanceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class WeeklyAttendanceReportResource extends JsonResource
{
    public function toArray($request)
    {

        $time =  $this->check_out_at ?  $this->check_out_at :\Carbon\Carbon::now();

          return  [
                'attendance_date' => $this->attendance_date,
                'week_day_in_number' => date('w', strtotime($this->attendance_date)),
                'week_day' => AttendanceHelper::getWeekDayFromDate($this->attendance_date),
                'check_in' => $this->check_in_at ? AttendanceHelper::changeTimeFormatForAttendanceView($this->check_in_at) : '-',
                'check_out' => $this->check_out_at ? AttendanceHelper::changeTimeFormatForAttendanceView($this->check_out_at) : '-',
                'productive_time_in_min' => \Carbon\Carbon::createFromFormat('H:i:s', $this->check_in_at)->diffInMinutes($time)
          ];

    }
}















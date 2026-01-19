<?php

namespace App\Resources\Attendance;

use App\Helpers\AttendanceHelper;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class WeeklyAttendanceTransformer
{
    private $weeklyAttendanceReport;

    public function __construct($weeklyAttendanceReport)
    {
        $this->weeklyAttendanceReport = $weeklyAttendanceReport;
    }

    public function transform()
    {
        return $this->handle($this->weeklyAttendanceReport);
    }

    private function handle($weeklyAttendanceReport)
    {
        $weeklyReport = [];
        $attendanceDetail = [];

        foreach ($weeklyAttendanceReport as $key => $value) {
            $attendanceDetail[date('w', strtotime($value['attendance_date']))] = $value;
        }

        for ($i = 0; $i < 7; $i++) {
            if (isset($attendanceDetail[$i])) {
                $attendanceDate = Carbon::parse($attendanceDetail[$i]['attendance_date']);
                $today = Carbon::today();

                $checkOutAt = $attendanceDetail[$i]['check_out_at'] ?? null;
                $nightCheckout = $attendanceDetail[$i]['night_checkout'] ?? null;

                if ((!$checkOutAt && !$nightCheckout) && $attendanceDate != $today) {
                    $time = null;
                } else {
                    $time = $checkOutAt ?? ($nightCheckout ?? Carbon::now());
                }


                if ($time) {
                    if (isset($attendanceDetail[$i]['check_in_at'])) {
                        $checkInTime = Carbon::parse($attendanceDetail[$i]['check_in_at']);
                        $productiveTimeInMin = $checkInTime->diffInMinutes(Carbon::parse($time));
                    } elseif (isset($attendanceDetail[$i]['night_checkin'])) {
                        $nightCheckIn = Carbon::parse($attendanceDetail[$i]['night_checkin']);
                        $nightCheckOut = Carbon::parse($attendanceDetail[$i]['night_checkout'] ?? Carbon::now());

                        // Ensure that both nightCheckIn and nightCheckOut are Carbon instances
                        $productiveTimeInMin = $nightCheckIn->diffInMinutes($nightCheckOut);
                    } else {
                        $productiveTimeInMin = 0;
                    }
                } else {
                    $productiveTimeInMin = 0;
                }
                $weeklyReport[] = [
                    'week_day' => AttendanceHelper::getWeekDayFromDate($attendanceDetail[$i]['attendance_date']),
                    'week_day_in_number' => date('w', strtotime($attendanceDetail[$i]['attendance_date'])),
                    'attendance_date' => $attendanceDetail[$i]['attendance_date'],
                    'check_in' => isset($attendanceDetail[$i]['check_in_at']) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendanceDetail[$i]['check_in_at']) : (isset($attendanceDetail[$i]['night_checkin']) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($attendanceDetail[$i]['night_checkin']) : '-'),
                    'check_out' => isset($attendanceDetail[$i]['check_out_at']) ? AttendanceHelper::changeTimeFormatForAttendanceView($attendanceDetail[$i]['check_out_at']) : (isset($attendanceDetail[$i]['night_checkout']) ? AttendanceHelper::changeNightTimeFormatForAttendanceView($attendanceDetail[$i]['night_checkout']) : '-'),
                    'productive_time_in_min' => $productiveTimeInMin,
                ];
            } else {
                $weeklyReport[] = null;
            }
        }

        return $weeklyReport;
    }
}



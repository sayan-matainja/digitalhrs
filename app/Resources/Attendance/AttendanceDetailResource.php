<?php

namespace App\Resources\Attendance;

use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $extraData = AttendanceHelper::getOverAndUnderTimeData($this);

        return [
            'id' => $this->id,
            'attendance_date' => AppHelper::dateInDDMMFormat($this->attendance_date, false),
            'attendance_date_nepali' => AppHelper::dateInDDMMFormat($this->attendance_date),
            'week_day' => AttendanceHelper::getWeekDayInShortForm($this->attendance_date),
            'check_in' => isset($this->check_in_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($this->check_in_at) : (isset($this->night_checkin) ? AttendanceHelper::changeTimeFormatForAttendanceView($this->night_checkin) : '-'),
            'check_out' => isset($this->check_out_at) ? AttendanceHelper::changeTimeFormatForAttendanceView($this->check_out_at) : (isset($this->night_checkout) ? AttendanceHelper::changeTimeFormatForAttendanceView($this->night_checkout) : '-'),
            'worked_hours_min' => (double)$this->worked_hour  ?? 0,
            'worked_hours' => $this->worked_hour  ? floor($this->worked_hour / 60) . 'h ' . round(($this->worked_hour - floor($this->worked_hour / 60) * 60)) . 'm': '0h 0m',
            'working_hours_min' => (double)$extraData['workingHourMin']  ?? 0,
            'working_hours' => $extraData['workingHourMin']  ? floor($extraData['workingHourMin'] / 60) . 'h ' . round(($extraData['workingHourMin'] - floor($extraData['workingHourMin'] / 60) * 60)) . 'm': '0h 0m',
            'overtime' => isset($extraData['overTime']) ? (floor($extraData['overTime'] / 60) . 'h ' . round(($extraData['overTime'] - floor($extraData['overTime'] / 60) * 60)) . 'm') : '',
            'is_overtime' => $extraData['isOverTime'],
            'undertime' => isset($this->check_out_at) ? (floor($extraData['underTime'] / 60) . 'h ' . round(($extraData['underTime'] - floor($extraData['underTime'] / 60) * 60)) . 'm') : '',
            'is_undertime' => $extraData['isUnderTime'],
        ];
    }
}














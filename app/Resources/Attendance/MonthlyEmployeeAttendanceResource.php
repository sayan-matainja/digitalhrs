<?php

namespace App\Resources\Attendance;


use App\Helpers\AppHelper;
use App\Helpers\AttendanceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyEmployeeAttendanceResource extends JsonResource
{
    /**
     * @param $request
     * @param $attendanceSummary
     * @return array
     */
    public function toArray($request)
    {
        $isBsEnabled = AppHelper::ifDateInBsEnabled();
        $data['user_detail'] = [
            'user_id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];



        $user_id = $this->id;
        if ($this->employeeTodayAttendance) {
            $data['employee_today_attendance'] = [
                'check_in_at' => $this->employeeTodayAttendance->check_in_at ? AttendanceHelper::changeTimeFormatForAttendanceView($this->employeeTodayAttendance->check_in_at) : '-',
                'check_out_at' => $this->employeeTodayAttendance->check_out_at ? AttendanceHelper::changeTimeFormatForAttendanceView($this->employeeTodayAttendance->check_out_at) : '-',
            ];
        } else {
            $data['employee_today_attendance'] = [
                'check_in_at' => '-',
                'check_out_at' =>  '-',
            ];
        }
        if ($this->employeeAttendance->count() > 0) {
            $data['employee_attendance'] = new EmployeeAttendanceDetailCollection($this->employeeAttendance);
        } else {
            $data['employee_attendance'] = [];
        }

//        $data['attendance_summary'] = [
//            'totalDays' => $attendanceSummary->totalDays,
//            'totalWeekend' => $attendanceSummary->totalWeekend,
//            'totalPresent' => $attendanceSummary->totalPresent ,
//            'totalHoliday' => $attendanceSummary->totalHoliday ,
//            'totalAbsent' => $attendanceSummary->totalAbsent ,
//            'totalLeave' => $attendanceSummary->totalLeave,
//            'totalWorkedHours' => $attendanceSummary->totalWorkedHours,
//            'totalWorkingHours' => $attendanceSummary->totalWorkingHours,
//        ];


        return $data;
    }
}















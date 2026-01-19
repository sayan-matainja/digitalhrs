<?php

namespace App\Resources\Dashboard;


use App\Resources\Attendance\WeeklyAttendanceTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeWeeklyReport extends JsonResource
{
    public function toArray($request)
    {
        $weeklyAttendanceObj = $this->employeeWeeklyAttendance()->get();
        return (new WeeklyAttendanceTransformer($weeklyAttendanceObj))->transform();
    }
}















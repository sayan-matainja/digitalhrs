<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceReportExport  implements WithMultipleSheets
{
    protected $attendanceData;
    protected $isBsEnabled;

    public function __construct($attendanceData, $isBsEnabled)
    {
        $this->attendanceData = $attendanceData;
        $this->isBsEnabled = $isBsEnabled;
    }

    public function sheets(): array
    {
        $sheets = [];
        $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
        $multipleAttendance = AppHelper::getAttendanceLimit();

        foreach ($this->attendanceData as $userId => $userData) {
            $userName = AppHelper::findUserName($userId);
            $sheets[] = new EmployeeAttendanceExport($userData, $userId, $this->isBsEnabled, $appTimeSetting, $multipleAttendance, $userName);
        }

        return $sheets;
    }

}


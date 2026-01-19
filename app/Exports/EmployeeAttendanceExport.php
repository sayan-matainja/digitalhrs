<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class EmployeeAttendanceExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $attendanceData;
    protected $userId;
    protected $isBsEnabled;
    protected $appTimeSetting;
    protected $multipleAttendance;
    protected $userName;

    public function __construct($attendanceData, $userId, $isBsEnabled, $appTimeSetting, $multipleAttendance, $userName)
    {
        $this->attendanceData = $attendanceData;
        $this->userId = $userId;
        $this->isBsEnabled = $isBsEnabled;
        $this->appTimeSetting = $appTimeSetting;
        $this->multipleAttendance = $multipleAttendance;
        $this->userName = $userName;
    }

    public function view(): View
    {
        return view('admin.attendance.export.attendance-report-export', [
            'attendanceData' => $this->attendanceData,
            'isBsEnabled' => $this->isBsEnabled,
            'appTimeSetting' => $this->appTimeSetting,
            'multipleAttendance' => $this->multipleAttendance,
            'userName' => $this->userName,
            'userId' => $this->userId,
        ]);
    }
    public function title(): string
    {
        return $this->userName;
    }


}


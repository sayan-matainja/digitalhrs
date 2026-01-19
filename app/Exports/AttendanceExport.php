<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceExport implements FromView, ShouldAutoSize
{
    protected $attendanceRecord;
    protected $userDetail;
    protected $multipleAttendance;
    protected $isBsEnabled;

    function __construct($attendanceRecord,$userDetail, $multipleAttendance, $isBsEnabled)
    {
        $this->attendanceRecord = $attendanceRecord;
        $this->userDetail = $userDetail;
        $this->multipleAttendance = $multipleAttendance;
        $this->isBsEnabled = $isBsEnabled;

    }

    public function view(): View
    {
        $appTimeSetting = AppHelper::check24HoursTimeAppSetting();
        return view('admin.attendance.export.attendance-export', [
            'attendanceRecordDetail' => $this->attendanceRecord,
            'employeeDetail' => $this->userDetail,
            'appTimeSetting'=>$appTimeSetting,
            'multipleAttendance'=> $this->multipleAttendance,
            'isBsEnabled'=> $this->isBsEnabled,
        ]);
    }

}

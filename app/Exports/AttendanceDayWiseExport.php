<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceDayWiseExport implements FromView, ShouldAutoSize
{
    protected $attendanceDayWiseRecord;
    protected $filterParameter;
    protected $multipleAttendance;
    protected $isBsEnabled;

    function __construct($attendanceDayWiseRecord,$filterParameter,  $multipleAttendance, $isBsEnabled)
    {
        $this->attendanceDayWiseRecord = $attendanceDayWiseRecord;
        $this->filterParameter = $filterParameter;
        $this->multipleAttendance = $multipleAttendance;
        $this->isBsEnabled = $isBsEnabled;
    }

    public function view(): View
    {
        $appTimeSetting = AppHelper::check24HoursTimeAppSetting();

        return view('admin.attendance.export.attendance-day-wise-export', [
            'attendanceDayWiseRecord' => $this->attendanceDayWiseRecord,
            'dayDetail' => $this->filterParameter,
            'appTimeSetting'=>$appTimeSetting,
            'multipleAttendance'=>$this->multipleAttendance,
            'isBsEnabled'=>$this->isBsEnabled,
        ]);
    }

}


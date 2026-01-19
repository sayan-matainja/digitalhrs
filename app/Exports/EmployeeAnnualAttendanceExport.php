<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class EmployeeAnnualAttendanceExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $attendanceData;
    protected $userName;
    protected $year;

    public function __construct($attendanceData, $userName, $year)
    {
        $this->attendanceData = $attendanceData;
        $this->userName = $userName;
        $this->year = $year;
    }

    public function view(): View
    {
        $totals = [
            'total_days'   => $this->attendanceData->sum('total_days'),
            'working_days' => $this->attendanceData->sum('working_days'),
            'present'      => $this->attendanceData->sum('present'),
            'holidays'     => $this->attendanceData->sum('holidays'),
            'weekends'     => $this->attendanceData->sum('weekends'),
            'leave'        => $this->attendanceData->sum('leave'),
            'absent'       => $this->attendanceData->sum('absent'),
        ];

        return view('admin.attendance.export.annual-attendance-report-export', [
            'attendanceData' => $this->attendanceData,
            'userName'       => $this->userName,
            'totals'         => $totals,
            'year'         => $this->year,
        ]);
    }

    public function title(): string
    {
        return substr($this->userName, 0, 31); // Excel sheet name max 31 chars
    }
}


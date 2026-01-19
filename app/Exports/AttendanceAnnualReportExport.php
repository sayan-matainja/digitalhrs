<?php

namespace App\Exports;

use App\Helpers\AppHelper;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceAnnualReportExport  implements WithMultipleSheets
{
    protected $attendanceData;
    protected $year;

    public function __construct($attendanceData, $year)
    {
        $this->attendanceData = $attendanceData;
        $this->year = $year;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->attendanceData as $userId => $userMonthlyData) {
            // Extract user name safely from first record (all have same name)
            $userName = $userMonthlyData->first()['user_name'] ?? 'Unknown User';

            $sheets[] = new EmployeeAnnualAttendanceExport($userMonthlyData, $userName, $this->year);
        }

        return $sheets;
    }
}

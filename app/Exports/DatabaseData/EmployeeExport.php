<?php

namespace App\Exports\DatabaseData;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EmployeeExport implements FromView, ShouldAutoSize
{
    protected $employeeDetail;

    function __construct($employeeDetail)
    {
        $this->employeeDetail = $employeeDetail;
    }

    public function view(): View
    {
        return view('exportData.user', [
            'employeeDetail' => $this->employeeDetail
        ]);
    }
}

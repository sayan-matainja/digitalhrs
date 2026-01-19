<?php

namespace App\Exports\DatabaseData;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LeaveTypeExport  implements FromView, ShouldAutoSize
{
    protected $leaveTypeDetail;

    function __construct($leaveTypeDetail){
        $this->leaveTypeDetail = $leaveTypeDetail;
    }
    public function view(): View
    {
        return view('exportData.leave-type', [
            'types' => $this->leaveTypeDetail
        ]);
    }
}

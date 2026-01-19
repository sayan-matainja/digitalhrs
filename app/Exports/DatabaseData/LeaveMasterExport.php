<?php

namespace App\Exports\DatabaseData;

use App\Models\LeaveRequestMaster;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveMasterExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function collection()
    {
        $LeaveRequestsCollection = new Collection();
        $select = ['id', 'no_of_days', 'leave_type_id', 'leave_requested_date', 'leave_from', 'leave_to', 'status', 'requested_by'];
        $with = ['leaveRequestedBy:id,name', 'leaveType:id,name'];
        LeaveRequestMaster::with($with)->select($select)
            ->chunk(100, function ($leaveRequests) use ($LeaveRequestsCollection) {
                foreach ($leaveRequests as $data) {
                    $LeaveRequestsCollection->push([
                        'id' => $data->id,
                        'no_of_days ' => $data->no_of_days,
                        'leave_type' => isset($data->leaveType) ? $data->leaveType->name : 'Time Leave',
                        'leave_requested_date' => $data->leave_requested_date,
                        'leave_from' => $data->leave_from,
                        'leave_to' => $data->leave_to,
                        'status' => $data->status,
                        'requested_by' => $data->leaveRequestedBy->name ?? '',
                    ]);
                }
            });
       return $LeaveRequestsCollection;
    }

    public function headings(): array
    {
        return [
            'Id',
            'No of Days',
            'Leave Type',
            'Leave Request Date',
            'Leave From',
            'Leave To',
            'Status',
            'Requested By'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

<?php

namespace App\Exports\DatabaseData;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceCollectionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function collection()
    {
        $select = ['id', 'user_id', 'attendance_date', 'check_in_at', 'check_out_at', 'check_in_latitude', 'check_out_latitude', 'check_in_longitude',
            'check_out_longitude', 'attendance_status'];
        $with = ['employee:id,name'];
        $attendanceCollection = new Collection();
        Attendance::with($with)->select($select)
            ->chunk(100, function ($attendances) use ($attendanceCollection) {
                foreach ($attendances as $data) {
                    $attendanceCollection->push([
                        'id' => $data->id,
                        'user_id' => $data->employee->name ?? '',
                        'attendance_date' => $data->attendance_date,
                        'check_in_at' => $data->check_in_at,
                        'check_out_at' => $data->check_out_at,
                        'check_in_latitude' => $data->check_in_latitude,
                        'check_out_latitude' => $data->check_out_latitude,
                        'check_in_longitude' => $data->check_in_longitude,
                        'check_out_longitude' => $data->check_out_longitude,
                        'attendance_status' => $data->attendance_status
                    ]);
                }
            });
        return $attendanceCollection;
    }

    public function headings(): array
    {
        return [
            'Id',
            'User Name',
            'Attendance Date',
            'Check In At',
            'Check Out At',
            'Check In latitude',
            'Check Out latitude',
            'Check In longitude',
            'Check Out longitude',
            'Attendance Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

}

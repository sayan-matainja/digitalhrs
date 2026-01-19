<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricAttendanceLog extends Model
{
    use HasFactory;
    protected $table = 'biometric_attendance_logs';

    protected $fillable = [
        'sn', 'table', 'stamp', 'employee_id', 'timestamp', 'attendance_status', 'data_receive_status', 'workspace_id', 'status4', 'status5'
    ];

    const RECORDS_PER_PAGE = 20;


    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

}

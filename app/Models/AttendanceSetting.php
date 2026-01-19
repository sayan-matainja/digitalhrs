<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $table = 'attendance_settings';

    protected $casts = [
        'values' => 'array',
    ];

    const ATTENDANCE_METHOD = ['default', 'biometric', 'nfc','qr'];

    protected $fillable = [
        'name',
        'slug',
        'value',
        'values',
        'status'
    ];

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricDeviceLog extends Model
{
    use HasFactory;
    protected $table = 'biometric_device_logs';

    protected $fillable = [
        'data', 'tgl', 'sn', 'option', 'url',
    ];

    const RECORDS_PER_PAGE = 20;

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometricFingerLog extends Model
{
    use HasFactory;

    protected $table = 'biometric_finger_logs';

    protected $fillable = [
        'data', 'url'
    ];

    const RECORDS_PER_PAGE = 20;
}

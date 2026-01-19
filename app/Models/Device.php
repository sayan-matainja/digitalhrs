<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $fillable = [
        'branch_id',
        'name',
        'serial_number',
        'ip_address',
        'last_online',
        'status'
    ];

    const RECORDS_PER_PAGE = 20;

}

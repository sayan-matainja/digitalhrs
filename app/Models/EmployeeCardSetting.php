<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCardSetting extends Model
{
    use HasFactory;

    protected $table = 'employee_card_settings';

    protected $fillable = [
        'name',
        'key',
        'value',
        'type',
        'options',
        'description'
    ];
}

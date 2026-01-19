<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PF extends Model
{

    protected $table = 'pf';

    protected $fillable = [
        'office_contribution', 'employee_contribution','is_active','applicable_date'
    ];
    
}

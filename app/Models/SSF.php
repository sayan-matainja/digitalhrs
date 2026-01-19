<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SSF extends Model
{
    use HasFactory;

    protected $table = 'ssf';

    protected $fillable = [
        'office_contribution', 'employee_contribution','is_active','applicable_date','enable_tax_exemption'
    ];
}

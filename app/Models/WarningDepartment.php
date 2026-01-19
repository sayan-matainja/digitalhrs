<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarningDepartment extends Model
{
    use HasFactory;

    protected $table = 'warning_departments';

    public $timestamps = false;

    protected $fillable = [
        'warning_id',
        'department_id'
    ];

    public function warning()
    {
        return $this->belongsTo(Warning::class, 'warning_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OverTimeDepartment extends Model
{
    use HasFactory;

    protected $table = 'over_time_departments';

    public $timestamps = false;

    protected $fillable = [
        'over_time_setting_id',
        'department_id'
    ];

    public function overTimeSetting()
    {
        return $this->belongsTo(OverTimeSetting::class, 'over_time_setting_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

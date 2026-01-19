<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryGroupDepartment extends Model
{
    use HasFactory;

    protected $table = 'salary_group_departments';

    public $timestamps = false;

    protected $fillable = [
        'salary_group_id',
        'department_id'
    ];

    public function salaryGroup()
    {
        return $this->belongsTo(SalaryGroup::class, 'salary_group_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

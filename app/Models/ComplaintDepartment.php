<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintDepartment extends Model
{
    use HasFactory;

    protected $table = 'complaint_departments';

    public $timestamps = false;

    protected $fillable = [
        'complaint_id',
        'department_id'
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

}

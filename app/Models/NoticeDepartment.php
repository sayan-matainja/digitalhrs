<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoticeDepartment extends Model
{
    use HasFactory;

    protected $table = 'notice_departments';

    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'department_id'
    ];

    public function notice_id()
    {
        return $this->belongsTo(Notice::class, 'notice_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingDepartment extends Model
{
    use HasFactory;
    protected $table = 'training_departments';

    public $timestamps = false;

    protected $fillable = [
        'training_id',
        'department_id'
    ];

    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}

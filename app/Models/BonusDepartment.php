<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusDepartment extends Model
{
    use HasFactory;
    protected $table = 'bonus_departments';

    public $timestamps = false;

    protected $fillable = [
        'bonus_id',
        'department_id'
    ];

    public function bonus()
    {
        return $this->belongsTo(Bonus::class, 'bonus_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

}

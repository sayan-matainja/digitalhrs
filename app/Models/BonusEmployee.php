<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusEmployee extends Model
{
    use HasFactory;

    protected $table = 'bonus_employees';

    public $timestamps = false;

    protected $fillable = [
        'bonus_id',
        'employee_id'
    ];

    public function bonus()
    {
        return $this->belongsTo(Bonus::class, 'bonus_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}

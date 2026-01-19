<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarningEmployee extends Model
{
    use HasFactory;
    protected $table = 'warning_employees';

    public $timestamps = false;

    protected $fillable = [
        'warning_id',
        'employee_id'
    ];

    public function warning()
    {
        return $this->belongsTo(Warning::class, 'warning_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

}

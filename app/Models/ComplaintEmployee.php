<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintEmployee extends Model
{
    use HasFactory;

    protected $table = 'complaint_employees';

    public $timestamps = false;

    protected $fillable = [
        'complaint_id',
        'employee_id'
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}

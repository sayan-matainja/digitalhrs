<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryGroupEmployee extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'salary_group_employees';

    protected $fillable = [
        'salary_group_id',
        'employee_id'
    ];

    public function salaryGroup(): BelongsTo
    {
        return $this->belongsTo(SalaryGroup::class, 'salary_group_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}

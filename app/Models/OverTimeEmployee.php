<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OverTimeEmployee extends Model
{
    use HasFactory;

    protected $table = 'over_time_employees';

    protected $fillable = [
        'over_time_setting_id', 'employee_id'
    ];


    public function overTimeSetting(): BelongsTo
    {
        return $this->belongsTo(OverTimeSetting::class, 'over_time_setting_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnderTimeEmployee extends Model
{
    use HasFactory;

    protected $table = 'under_time_employees';

    protected $fillable = [
        'under_time_setting_id', 'employee_id'
    ];


    public function underTimeSetting(): BelongsTo
    {
        return $this->belongsTo(UnderTimeSetting::class, 'under_time_setting_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}

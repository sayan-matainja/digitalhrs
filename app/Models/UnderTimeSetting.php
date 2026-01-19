<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnderTimeSetting extends Model
{
    use HasFactory;

    protected $table = 'under_time_settings';

    protected $fillable = [
        'branch_id','title','applied_after_minutes', 'ut_penalty_rate', 'is_active','penalty_type','penalty_percent'
    ];

    public function utEmployees(): HasMany
    {
        return $this->hasMany(UnderTimeEmployee::class, 'under_time_setting_id', 'id');
    }
}

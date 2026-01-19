<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    use HasFactory;
    protected $table = 'bonuses';
    protected $casts = [
        'apply_for_all' => 'boolean',
    ];
    protected $fillable = [
        'branch_id','title', 'description', 'value_type', 'value', 'applicable_month', 'is_active','apply_for_all'
    ];
    public function bonusEmployee()
    {
        return $this->hasMany(BonusEmployee::class,'bonus_id','id');
    }
    public function bonusDepartment()
    {
        return $this->hasMany(BonusDepartment::class,'bonus_id','id');
    }
}

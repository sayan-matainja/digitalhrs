<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

class SalaryGroup extends Model
{
    use HasFactory;

    protected $table = 'salary_groups';

    protected $fillable = [
        'branch_id',
        'name',
        'slug',
        'is_active',
        'created_by',
        'updated_by',

    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
        });

        static::deleting(function($salaryGroupDetail) {
            $salaryGroupDetail->groupEmployees()->delete();
            $salaryGroupDetail->groupDepartment()->delete();
            $salaryGroupDetail->salaryComponents()->detach();
        });
    }

    public function salaryComponents(): BelongsToMany
    {
        return $this->belongsToMany(SalaryComponent::class,
            'salary_group_component',
            'salary_group_id',
            'salary_component_id',
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function groupEmployees(): HasMany
    {
        return $this->hasMany(SalaryGroupEmployee::class, 'salary_group_id', 'id');
    }
    public function groupDepartment()
    {
        return $this->hasMany(SalaryGroupDepartment::class,'salary_group_id','id');
    }


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

class SalaryTDS extends Model
{
    use HasFactory;

    protected $table = 'salary_t_d_s';

    public $timestamps = false;

    protected $fillable = [
        'annual_salary_from',
        'annual_salary_to',
        'tds_in_percent',
        'marital_status',
        'status',
        'created_by',
        'updated_by'
    ];

    const MARITAL_STATUS = [
        'single',
        'married'
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
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status',true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SalaryReviseHistory extends Model
{
    use HasFactory;

    protected $table = 'salary_revise_histories';

    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'salary_revised_on',
        'increment_amount',
        'revised_salary',
        'base_salary',
        'remark',
        'created_by',
        'updated_by',
        'increment_percent',
        'fiscal_year_id',
        'date_from',
        'date_to',
        'base_monthly_salary',
        'base_weekly_salary',
        'base_monthly_allowance',
        'base_weekly_allowance',
        'hour_rate',
        'weekly_hours',
        'monthly_hours'
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

    public function remark(): Attribute
    {
        return new Attribute(
            get: fn($value) => strip_tags($value)
        );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}

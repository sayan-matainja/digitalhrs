<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class OfficeTime extends Model
{
    use HasFactory;

    protected $table = 'office_times';

    const CATEGORY = ['full_timer', 'part_timer'];

    const RECORD_PER_PAGE = 10;

    protected $fillable = [
        'company_id',
        'opening_time',
        'closing_time',
        'shift',
        'category',
        'holiday_count',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'is_early_check_in',
        'checkin_before',
        'is_early_check_out',
        'checkout_before',
        'is_late_check_in',
        'checkin_after',
        'is_late_check_out',
        'checkout_after',
        'shift_type',
        'branch_id',
        'halfday_mark_time'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
            $model->updated_by = Auth::user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
        });

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                 $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    /**
     * @return Attribute
     */
    protected function closingTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => date("g:i A", strtotime($value)),
        );
    }

    /**
     * @return Attribute
     */
    protected function openingTime(): Attribute
    {
        return Attribute::make(
            get: fn($value) => date("g:i A", strtotime($value)),
        );
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'office_time_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}

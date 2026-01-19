<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveType extends Model
{
    use HasFactory;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'slug',
        'leave_allocated',
        'company_id',
        'is_active',
        'early_exit',
        'created_by',
        'updated_by',
        'branch_id',
        'gender',
        'is_carried',
        'carry_limit'
    ];

    const RECORDS_PER_PAGE = 20;

    const IS_ACTIVE = 1;


    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
        });

        static::addGlobalScope('branch', function (Builder $builder) {
            if (auth('admin')->check()) {
                // Admin can see all branches
                return;
            }

            if (auth()->check() && auth()->user()->branch_id) {
                $builder->whereHas('branch', function ($query) {
                    $query->where('id', auth()->user()->branch_id);
                });
            }
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }


    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}

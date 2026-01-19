<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    use HasFactory;

    const RECORDS_PER_PAGE = 20;

    const TYPES = [
        'general',
        'comment',
        'project',
        'task',
        'attendance',
        'leave',
        'support',
        'tada',
        'event',
        'training',
        'complaint',
        'warning',
        'asset',
        'loan',
        'holiday',
        'payroll',
        'resignation',
        'termination',
        'meeting'
    ];

    protected $table = 'notifications';

    protected $fillable = [
        'title',
        'description',
        'type',
        'notification_for_id',
        'notification_publish_date',
        'company_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
            $model->notification_publish_date = Carbon::now()->format('Y-m-d H:i:s');
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
            $model->notification_publish_date = Carbon::now()->format('Y-m-d H:i:s');
        });

        static::deleting(function($notifiedUserDetail){
            $notifiedUserDetail->notifiedUsers()->delete();
        });
        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('createdBy', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

            });
        }
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function notifiedUsers(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'notification_id', 'id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class LeaveApproval extends Model
{
    use HasFactory;
    protected $table = 'leave_approvals';

    protected $fillable = ['subject', 'leave_type_id', 'max_days_limit','status','branch_id'];

    const RECORDS_PER_PAGE = 10;

    public static function boot()
    {
        parent::boot();

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                 $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }
    }

    public function approvalDepartment(): HasMany
    {
        return $this->hasMany(LeaveApprovalDepartment::class, 'leave_approval_id', 'id');
    }

    public function approvalRole(): HasMany
    {
        return $this->hasMany(LeaveApprovalRole::class, 'leave_approval_id', 'id');
    }
    public function notificationReceiver(): HasMany
    {
        return $this->hasMany(LeaveApprovalNotificationRecipient::class, 'leave_approval_id', 'id');
    }
    public function approvalProcess(): HasMany
    {
        return $this->hasMany(LeaveApprovalProcess::class, 'leave_approval_id', 'id');
    }
    public function leaveType():BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}


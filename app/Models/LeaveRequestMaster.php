<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class LeaveRequestMaster extends Model
{
    use HasFactory;

    protected $table = 'leave_requests_master';

    protected $fillable = [

        'leave_type_id',
        'no_of_days',
        'leave_requested_date',
        'leave_from',
        'leave_to',
        'reasons',
        'status',
        'admin_remark',
        'company_id',
        'requested_by',
        'early_exit',
        'request_updated_by',
        'referred_by',
        'start_time',
        'end_time',
        'title',
        'branch_id',
        'department_id',
        'leave_for',
        'leave_in',
        'cancel_request',
        'cancellation_approved_at',
        'cancellation_approved_by',
        'cancellation_reason'
    ];

    const RECORDS_PER_PAGE = 20;

    const STATUS = ['pending','approved','rejected','cancelled'];

    public static function boot()
    {
        parent::boot();

//        static::creating(function ($model) {
//            $model->requested_by = Auth::user()->id;
//        });

        static::updating(function ($model) {
            $model->request_updated_by = Auth::check() ? Auth::user()->id : null;
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

    public function leaveRequestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    public function leaveRequestUpdatedBy()
    {
        return $this->belongsTo(User::class, 'request_updated_by', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by', 'id');
    }

    public function leaveApproval()
    {
        return $this->belongsTo(LeaveApproval::class, 'leave_type_id', 'leave_type_id');
    }

    // Relationship to LeaveApprovalProcess
    public function approvalProcess()
    {
        return $this->hasMany(LeaveApprovalProcess::class, 'leave_approval_id', 'leave_type_id');
    }
    public function requestApproval()
    {
        return $this->hasMany(LeaveRequestApproval::class, 'leave_request_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

}

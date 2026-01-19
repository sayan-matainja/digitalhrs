<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestApproval extends Model
{
    use HasFactory;

    protected $table = 'leave_request_approvals';

    protected $fillable = [
        'leave_request_id',
        'status',
        'approved_by',
        'reason'
    ];



    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequestMaster::class, 'leave_request_id', 'id');
    }
}

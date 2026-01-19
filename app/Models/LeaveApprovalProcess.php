<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApprovalProcess extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'leave_approval_processes';

    protected $fillable = [
        'leave_approval_id',
        'approver',
        'role_id',
        'user_id',
    ];

    public function leaveApproval():BelongsTo
    {
        return $this->belongsTo(LeaveApproval::class, 'leave_approval_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id')->select('name', 'id', 'slug');
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequestMaster::class, 'leave_approval_id', 'leave_type_id');
    }
}

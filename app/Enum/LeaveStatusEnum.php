<?php

namespace App\Enum;

enum LeaveStatusEnum:string
{
    case pending = 'pending';
    case approved = 'approved';
    case rejected = 'rejected';
    case cancelled = 'cancelled';
    case review = 'review';
}

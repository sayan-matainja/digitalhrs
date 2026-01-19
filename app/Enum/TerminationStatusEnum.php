<?php

namespace App\Enum;

enum TerminationStatusEnum:string
{
    case pending = 'pending';
    case approved = 'approved';
    case cancelled = 'cancelled';
    case onReview = 'onReview';
}

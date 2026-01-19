<?php

namespace App\Enum;

enum HrSetupStatusEnum:string
{
    case pending = 'pending';
    case approved = 'approved';
    case rejected = 'rejected';
    case onReview = 'onReview';
}

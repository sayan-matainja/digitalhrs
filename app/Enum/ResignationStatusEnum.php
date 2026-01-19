<?php

namespace App\Enum;

enum ResignationStatusEnum:string
{
    case pending = 'pending';
    case approved = 'approved';
    case cancelled = 'rejected';
    case onReview = 'onReview';
}

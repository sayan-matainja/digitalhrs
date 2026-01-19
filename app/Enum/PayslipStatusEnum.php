<?php

namespace App\Enum;

enum PayslipStatusEnum:string
{
    case generated = 'generated';
    case review = 'review';
    case locked = 'locked';
    case paid = 'paid';
}

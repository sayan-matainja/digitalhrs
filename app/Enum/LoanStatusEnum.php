<?php

namespace App\Enum;

enum LoanStatusEnum:string
{
    case pending = 'pending';
    case approve = 'approved';
    case reject = 'rejected';
    case settled = 'settled';

}

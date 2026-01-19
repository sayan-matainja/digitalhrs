<?php

namespace App\Enum;

enum LoanRepaymentStatusEnum:string
{
    case active = 'active';
    case upcoming = 'upcoming';
    case paid = 'paid';

}

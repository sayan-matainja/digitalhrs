<?php

namespace App\Enum;

enum LoanInterestTypeEnum:string
{
    case declining = 'declining';
    case fixed = 'fixed';

 public function getFormattedName(): string
    {
        return match($this) {
            self::declining => 'Declining',
            self::fixed => 'Fixed',
        };
    }

}

<?php

namespace App\Enum;

enum BonusTypeEnum:string
{
    case basic_percent = 'basic';
    case annual_percent = 'annual';
    case fixed = 'fixed';

    public function getFormattedName(): string
    {
        return match($this) {
            self::basic_percent => 'Basic Percent',
            self::annual_percent => 'Annual Percent',
            self::fixed => 'Fixed Amount',
        };
    }

}

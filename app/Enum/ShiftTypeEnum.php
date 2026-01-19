<?php

namespace App\Enum;

enum ShiftTypeEnum:string
{
    case morning = 'morning';
    case day = 'day';
    case evening = 'evening';
    case night = 'night';
}

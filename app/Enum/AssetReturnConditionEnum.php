<?php

namespace App\Enum;

enum AssetReturnConditionEnum:string
{
    case working = 'working';
    case requireMaintenance = 'maintenance';
    case repaired = 'repaired';
}

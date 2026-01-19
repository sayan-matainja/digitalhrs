<?php

namespace App\Enum;

enum AssetAssignmentStatusEnum:string
{
    case assigned = 'assigned';
    case returned = 'returned';
}

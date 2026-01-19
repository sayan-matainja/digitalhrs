<?php

namespace App\Enum;

enum LeaveApproverEnum:string
{
    case department_head = 'department_head';
    case supervisor = 'supervisor';
    case specific_personnel = 'specific_personnel';
}

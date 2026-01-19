<?php

namespace App\Enum;

enum EmployeeAttendanceTypeEnum:string
{
    case wifi = 'wifi';
    case nfc = 'nfc';
    case qr = 'qr';
    case biometric = 'biometric';
}

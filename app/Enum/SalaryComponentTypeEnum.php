<?php

namespace App\Enum;

enum SalaryComponentTypeEnum:string
{
    case adjustable='adjustable';
    case basic_percent = 'basic';
    case percent = 'ctc';
    case fixed = 'fixed';
}

<?php

namespace App\Enum;

enum EventStatusEnum:string
{
    case pending = 'pending';
    case completed = 'completed';
    case ongoing = 'ongoing';
    case cancelled = 'cancelled';
}

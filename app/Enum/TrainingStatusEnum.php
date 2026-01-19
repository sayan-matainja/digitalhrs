<?php

namespace App\Enum;

enum TrainingStatusEnum:string
{
    case pending = 'pending';
    case completed = 'completed';
    case ongoing = 'ongoing';
    case cancelled = 'cancelled';
}

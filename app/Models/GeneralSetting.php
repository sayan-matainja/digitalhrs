<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class GeneralSetting extends Model
{
    use HasFactory;

    protected $table = 'general_settings';

    protected $fillable = [
        'name',
        'type',
        'key',
        'value',
    ];

    const GENERAL_SETTING_KEY = ['firebase_key'];

}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    use HasFactory;

    protected $table = 'theme_settings';

    protected $fillable = [
        'primary_color',
        'hover_color',
        'dark_primary_color',
        'dark_hover_color',
    ];
}

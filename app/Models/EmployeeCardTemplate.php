<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCardTemplate extends Model
{
    use HasFactory;
    protected $table = 'employee_card_templates';
    protected $casts = [
        'front_background' => 'array',
        'back_background' => 'array',
        'front_layout' => 'array',
        'back_layout' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'is_default', 'orientation', 'front_background',
        'back_background', 'graph_type', 'graph_color', 'graph_field', 'term_conditions',
         'front_layout', 'back_layout'];


}

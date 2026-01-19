<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmployeeCardCustomField extends Model
{
    use HasFactory;
    protected $table = 'employee_card_custom_fields';
    protected $fillable = ['name', 'key', 'company_id', 'is_active'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->key)) {
                $model->key = Str::slug($model->name, '_');
            }
        });
    }

}

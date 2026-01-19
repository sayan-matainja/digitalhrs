<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCardUpload extends Model
{
    use HasFactory;
    protected $table = 'employee_card_uploads';
    const UPLOAD_PATH = 'uploads/card/';

    protected $fillable = [
        'front_logo', 'back_logo', 'signature_image', 'footer_text',
    ];

}

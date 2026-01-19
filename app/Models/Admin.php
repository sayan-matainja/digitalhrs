<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{

    protected $table = 'admins';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'password',
        'email',
        'username',
        'is_active',
        'avatar',
    ];

    const AVATAR_UPLOAD_PATH = 'uploads/admin/avatar/';
    const RECORDS_PER_PAGE = 20;
}

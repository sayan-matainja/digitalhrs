<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory;

    protected $table = "permission_groups";

    protected $fillable = [
        'name',
        'group_type_id'
    ];
    public $timestamps = false;

    public function getPermission(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Permission::class, 'permission_groups_id', 'id');
    }
}

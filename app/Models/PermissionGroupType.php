<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroupType extends Model
{
    use HasFactory;

    protected $table = "permission_group_types";

    protected $fillable = [
        'name',
        'slug'
    ];
    public $timestamps = false;

    public function permissionGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PermissionGroup::class, 'group_type_id', 'id');
    }
}

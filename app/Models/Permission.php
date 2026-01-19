<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "permissions";

    protected $fillable = [
        'name',
        'permission_key',
        'permission_groups_id'
    ];

    public function permissionGroup(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_groups_id', 'id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssignedMember extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'assigned_members';

    protected $fillable = [
        'member_id',
        'assignable_id',
        'assignable_type',
    ];

    public function assignable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'assignable_type', 'assignable_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'id');
    }
}

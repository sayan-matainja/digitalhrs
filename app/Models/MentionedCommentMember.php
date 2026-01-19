<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MentionedCommentMember extends Model
{
    use HasFactory;

    protected $table = 'mentioned_comment_members';

    protected $fillable = [
        'member_id',
        'mentionable_id',
        'mentionable_type',
    ];

    public function mentionable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'mentionable_type', 'mentionable_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'id');
    }
}

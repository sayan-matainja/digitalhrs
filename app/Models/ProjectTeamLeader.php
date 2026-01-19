<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTeamLeader extends Model
{
    use HasFactory;

    protected $table = 'project_team_leaders';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'leader_id'
    ];


    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id', 'id');
    }
}

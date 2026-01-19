<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChecklist extends Model
{
    use HasFactory;

    protected $table = 'task_checklists';

    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'name',
        'is_completed',
        'assigned_to'
     ];


    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id', 'id');
    }

    public function taskAssigned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }



}

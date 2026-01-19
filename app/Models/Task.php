<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'project_id',
        'name',
        'priority',
        'status',
        'start_date',
        'end_date',
        'description',
        'is_active',
        'created_by',
        'updated_by',
        'branch_id'
    ];

    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/tasks/';

    const STATUS = [
        'in_progress',
        'not_started',
        'cancelled',
        'completed',
        'not_completed'

    ];

    const PRIORITY = [
        'low',
        'medium',
        'high',
        'urgent'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
            $model->updated_by = Auth::user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
        });

        static::deleting(function ($taskDetail) {
            $taskDetail->assignedMembers()->delete();
            $taskDetail->taskAttachments()->delete();
        });

        if (Auth::check() && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });

            });
        }

    }

    public function assignedMembers(): MorphMany
    {
        return $this->morphMany(AssignedMember::class, 'assignable')->whereHas('user');
    }

    public function taskAttachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id')->latest();
    }

    public function taskChecklists(): HasMany
    {
        return $this->hasMany(TaskChecklist::class, 'task_id', 'id');
    }

    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id', 'id');
    }

    public function completedTaskChecklist(): HasMany
    {
        return $this->hasMany(TaskChecklist::class, 'task_id', 'id')
            ->where('is_completed',1);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function getTaskProgressInPercentage(): float|int
    {
        $totalChecklistCount  = $this->taskChecklists()->count();

        if($totalChecklistCount == 0 ){
            return 0;
        }

        $totalCompletedChecklistCount  = $this->completedTaskChecklist()->count();
        $taskProgress = ($totalCompletedChecklistCount / $totalChecklistCount) * 100;
        return ceil($taskProgress);
    }

    public function taskAssignedChecklists(): HasMany
    {
        $authCode = getAuthUserCode();

        $query = $this->hasMany(TaskChecklist::class, 'task_id', 'id');

        // Only apply the authentication filtering if $authCode exists
        if ($authCode) {
            $query->where(function($query) use ($authCode){
                $query->whereHas('taskAssigned', function ($subQuery) use ($authCode) {
                    $subQuery->where('assigned_to', $authCode);
                })->orWhereHas('task.project.projectLeaders', function ($subQuery) use ($authCode){
                    $subQuery->where('leader_id', $authCode);
                });
            });
        }

        return $query;
    }

    public function taskDuration(): int
    {
        $startDate = Carbon::parse($this->start_date);
        $date = Carbon::parse($this->end_date);
        return $date->diffInDays($startDate)+1;
    }
    public function taskRemainingDaysToComplete(): int
    {
        $now = Carbon::now();
        if($now > Carbon::parse($this->end_date)){
            return 0;
        }
        $date = Carbon::parse($this->end_date);
        return $date->diffInDays($now);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}

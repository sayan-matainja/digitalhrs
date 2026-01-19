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

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $casts = [
        'department_ids' => 'array',
    ];
    protected $fillable = [
        'name',
        'client_id',
        'start_date',
        'deadline',
        'cost',
        'estimated_hours',
        'status',
        'priority',
        'description',
        'cover_pic',
        'is_active',
        'created_by',
        'updated_by',
        'slug',
        'branch_id',
        'department_ids'
    ];

    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/projects/cover/';

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

        static::deleting(function ($projectDetail) {
            $projectDetail->assignedMembers()->delete();
            $projectDetail->projectLeaders()->delete();
            $projectDetail->tasks()->delete();
            $projectDetail->projectAttachments()->delete();
        });



        if (Auth::check()) {
            static::addGlobalScope('branch', function (Builder $builder) {
                $user = Auth::user();
                $branchId = $user->branch_id;
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

    public function projectAttachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id', 'id');
//            ->where('is_active',1)
//            ->latest();

    }

    public function completedTask(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id', 'id')
            ->whereIn('status',['completed','cancelled']);
    }

    public function projectLeaders(): HasMany
    {
        return $this->hasMany(ProjectTeamLeader::class, 'project_id', 'id')->whereHas('user');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function getProjectProgressInPercentage(): int|string
    {
        $totalTaskCount = $this->tasks()->count();
        if ($totalTaskCount < 1) {
            return 0;
        }
        $totalCompletedTaskCount = $this->completedTask()->count();
        $projectProgress = ($totalCompletedTaskCount / $totalTaskCount) * 100;
        return ceil($projectProgress);
    }

    public function getOnlyEmployeeAssignedTask(): Builder|HasMany
    {
        $authCode = getAuthUserCode();

        $query = $this->hasMany(Task::class, 'project_id', 'id');

        // Only apply the authentication filtering if $authCode exists
        if ($authCode) {
            $query->where(function($query) use ($authCode){
                $query->whereHas('assignedMembers.user', function ($subQuery) use ($authCode) {
                    $subQuery->where('id', $authCode);
                })
                    ->orWhereHas('project.projectLeaders', function ($subQuery) use ($authCode) {
                        $subQuery->where('leader_id', $authCode);
                    });
            })
                ->where('is_active', 1);

        }

        return $query->latest();

    }

    public function projectRemainingDaysToComplete(): int
    {
        $now = Carbon::now();
        if($now > Carbon::parse($this->deadline)){
            return 0;
        }
        $endDate = Carbon::parse($this->deadline);
        return $now->diffInDays($endDate);
    }
    public function projectDuration(): int
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->deadline);
        return $startDate->diffInDays($endDate)+1;
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }



}

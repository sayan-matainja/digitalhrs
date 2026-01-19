<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'name',
        'email',
        'contact_no',
        'avatar',
        'address',
        'country',
        'is_active',
        'created_by',
        'updated_by',
        'branch_id'
    ];

    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/client/';

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

        if (Auth::check()) {
            static::addGlobalScope('branch', function (Builder $builder) {
                $branchId = Auth::user()->branch_id;
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }

    }

    public static function filteredByBranch()
    {
        $query = static::query();
        $branchId = auth()->user() ? auth()->user()->branch_id : null;

        return $query->when(isset($branchId), function ($query) use ($branchId) {
            $query->where('clients.branch_id', $branchId);
        });
    }



    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'client_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}


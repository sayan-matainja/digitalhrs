<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Branch extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'branch_head_id',
        'company_id',
        'branch_location_latitude',
        'branch_location_longitude',
        'is_active',
        'created_by',
        'updated_by'
    ];

    const RECORDS_PER_PAGE = 10;

    const UPLOAD_PATH = 'uploads/branch/';

    const IS_ACTIVE = 1;

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

        if (Auth::check() ) {
            static::addGlobalScope('branch', function (Builder $builder) {
                $user = Auth::user();
                if (isset($user->branch_id)) {
                    $branchId = $user->branch_id;
                    $builder->where('id', $branchId);
                }
            });
        }

    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }

    public function branchHead(): BelongsTo
    {
        return $this->belongsTo(User::class,'branch_head_id','id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class,'company_id','id');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class,'branch_id','id');
    }

    public function routers(): HasMany
    {
        return $this->hasMany(Router::class,'branch_id','id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class,'branch_id','id')
            ->where([
                ['status', '=', 'verified'],
                ['is_active', '=', self::IS_ACTIVE ],
            ]);
    }

}

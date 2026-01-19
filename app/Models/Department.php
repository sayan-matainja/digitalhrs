<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'dept_name',
        'slug',
        'is_active',
        'dept_head_id',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by'
    ];

    const RECORDS_PER_PAGE = 10;

    const IS_ACTIVE = 1;

    const UPLOAD_PATH = 'uploads/department/';


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
                     $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
                }
            });
        }
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function departmentHead()
    {
        return $this->belongsTo(User::class, 'dept_head_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class,'dept_id','id')->select('id','post_name')->where('is_active',1);
    }

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class,'department_id','id')
            ->where([
                ['status', '=', 'verified'],
                ['is_active', '=', self::IS_ACTIVE ],
            ]);
    }

}


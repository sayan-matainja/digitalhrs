<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'post_name',
        'is_active',
        'dept_id',
        'branch_id',
    ];

    const RECORDS_PER_PAGE = 10;

    const IS_ACTIVE = 1;

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('branch', function (Builder $builder) {

            $user = Auth::user();
            if (isset($user->branch_id) && (isset($user->id) && $user->id != 1)) {
                $branchId = $user->branch_id;
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            }
        });
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'dept_id', 'id');
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class,'post_id','id')
          ->where([
          ['status', '=', 'verified'],
          ['is_active', '=', self::IS_ACTIVE ],
        ]);
    }

    public function hasEmployee()
    {
        return $this->hasMany(User::class,'post_id','id');
    }

}



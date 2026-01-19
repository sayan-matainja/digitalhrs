<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AssetAssignment extends Model
{
    use HasFactory;
    protected $table = 'asset_assignments';

    protected $fillable = [
        'asset_id','branch_id','department_id', 'user_id', 'status', 'assigned_date', 'returned_date', 'return_condition', 'notes'
    ];


    public static function boot()
    {
        parent::boot();


        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->where('branch_id', $branchId);
            });
        }
    }


    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class,'asset_id','id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class,'branch_id','id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id')->withDefault();
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Warning extends Model
{
    use HasFactory;
    protected $table = 'warnings';
    protected $fillable = [
        'branch_id', 'subject', 'warning_date', 'message', 'status', 'created_by', 'updated_by'
    ];


    const RECORDS_PER_PAGE = 20;

    public static function boot()
    {
        parent::boot();

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                 $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class,'branch_id','id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function warningEmployee()
    {
        return $this->hasMany(WarningEmployee::class,'warning_id','id');
    }
    public function warningDepartment()
    {
        return $this->hasMany(WarningDepartment::class,'warning_id','id');
    }
    public function warningReply()
    {
        return $this->hasMany(WarningResponse::class,'warning_id','id');
    }
}

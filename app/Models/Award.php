<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Award extends Model
{
    use HasFactory;

    protected $table = 'awards';
    protected $fillable = [
        'employee_id', 'award_type_id', 'gift_item', 'award_base', 'awarded_date', 'awarded_by', 'status', 'award_description',
        'gift_description', 'attachment', 'reward_code','branch_id','department_id'
    ];


    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/award/';

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

    public function type(): BelongsTo
    {
        return $this->belongsTo(AwardType::class,'award_type_id','id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

}

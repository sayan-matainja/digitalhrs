<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Tada extends Model
{
    use HasFactory;

    const RECORDS_PER_PAGE = 20;

    const STATUS = ['pending', 'accepted', 'rejected'];

    protected $table = 'tadas';

    protected $fillable = [
        'title',
        'description',
        'total_expense',
        'status',
        'is_active',
        'is_settled',
        'remark',
        'employee_id',
        'verified_by',
        'created_by',
        'updated_by',
        'branch_id',
        'department_id',
        'approved_date'
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

        static::deleting(function ($tadaDetail) {
            $tadaDetail->attachments()->delete();
        });

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');

    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TadaAttachment::class, 'tada_id', 'id');
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

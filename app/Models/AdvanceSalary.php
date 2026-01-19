<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AdvanceSalary extends Model
{
    use HasFactory;

    protected $table = 'advance_salaries';

    protected $fillable = [
        'employee_id',
        'requested_amount',
        'released_amount',
        'advance_requested_date',
        'amount_granted_date',
        'description',
        'is_settled',
        'status',
        'remark',
        'verified_by',
        'created_by',
    ];

    const RECORDS_PER_PAGE = 20;

    const STATUS = [
        'pending',
        'processing',
        'approved',
        'rejected',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->verified_by  = Auth::user()->id ?? null;
        });

        static::deleting(function ($advanceSalaryDetail) {
            $advanceSalaryDetail->attachments()->delete();
        });

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('requestedBy', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

            });
        }
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdvanceSalaryAttachment::class, 'advance_salary_id', 'id');
    }
}

?>


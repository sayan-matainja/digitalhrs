<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class Notice extends Model
{
    use HasFactory;

    protected $table = 'notices';

    protected $fillable = [
        'title',
        'description',
        'notice_publish_date',
        'company_id',
        'branch_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    const RECORDS_PER_PAGE = 20;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
            $model->notice_publish_date = Carbon::now()->format('Y-m-d H:i:s');
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
            $model->notice_publish_date = Carbon::now()->format('Y-m-d H:i:s');
        });


        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $user = Auth::user();

            static::addGlobalScope('branch', function (Builder $builder) use ($user) {
                $branchId = $user->branch_id;
                 $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
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

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function noticeReceiversDetail()
    {
        return $this->hasMany(NoticeReceiver::class, 'notice_id', 'id');
    }
    public function noticeDepartments()
    {
        return $this->hasMany(NoticeDepartment::class, 'notice_id', 'id')->whereHas('department');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

}

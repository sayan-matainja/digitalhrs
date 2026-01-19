<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'company_id',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'check_in_latitude',
        'check_out_latitude',
        'check_in_longitude',
        'check_out_longitude',
        'note',
        'edit_remark',
        'attendance_status',
        'created_by',
        'updated_by',
        'check_in_type',
        'check_out_type',
        'worked_hour',
        'overtime',
        'undertime',
        'check_in_note',
        'check_out_note',
        'night_checkin',
        'night_checkout',
        'office_time_id'
    ];

    const RECORDS_PER_PAGE = 20;

    const ATTENDANCE_APPROVED = 1;
    const ATTENDANCE_REJECTED = 0;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::user()->id ?? null;
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::user()->id ?? null;
        });

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $user = Auth::user();

            static::addGlobalScope('branch', function (Builder $builder) use($user){
                $branchId = $user->branch_id;
                $builder->whereHas('employee', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

            });
        }
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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

    public function officeTime()
    {
        return $this->belongsTo(OfficeTime::class, 'office_time_id', 'id');
    }


}

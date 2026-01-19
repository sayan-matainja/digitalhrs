<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'transfers';
    protected $fillable = [
        'old_branch_id', 'old_department_id','employee_id','branch_id', 'department_id', 'transfer_date', 'description', 'status', 'created_by', 'updated_by','remark','old_post_id'
        ,'post_id','old_office_time_id','office_time_id','old_supervisor_id','supervisor_id'
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
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id','id');
    }
    public function employee()
    {
        return $this->belongsTo(User::class,'employee_id','id');
    }
    public function post()
    {
        return $this->belongsTo(Post::class,'post_id','id');
    }
    public function supervisor()
    {
        return $this->belongsTo(User::class,'supervisor_id','id');
    }
    public function officeTime()
    {
        return $this->belongsTo(OfficeTime::class,'office_time_id','id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
    public function oldBranch()
    {
        return $this->belongsTo(Branch::class,'old_branch_id','id');
    }
    public function oldDepartment()
    {
        return $this->belongsTo(Department::class,'old_department_id','id');
    }

    public function oldPost()
    {
        return $this->belongsTo(Post::class,'old_post_id','id');
    }
    public function oldSupervisor()
    {
        return $this->belongsTo(User::class,'old_supervisor_id','id');
    }
    public function oldOfficeTime()
    {
        return $this->belongsTo(OfficeTime::class,'old_office_time_id','id');
    }
}

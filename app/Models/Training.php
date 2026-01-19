<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Training extends Model
{
    use HasFactory;
    protected $table = 'trainings';
    protected $fillable = [
        'training_type_id', 'branch_id', 'cost', 'start_date', 'start_time',
        'end_date', 'end_time', 'certificate', 'description', 'status', 'created_by', 'updated_by','venue'
    ];


    const RECORDS_PER_PAGE = 20;
    const UPLOAD_PATH = 'uploads/training/';


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

    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class,'training_type_id','id');
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

    public function employeeTraining()
    {
        return $this->hasMany(EmployeeTraining::class,'training_id','id');
    }
    public function trainingDepartment()
    {
        return $this->hasMany(TrainingDepartment::class,'training_id','id');
    }
    public function trainingInstructor()
    {
        return $this->hasMany(TrainingInstructor::class,'training_id','id');
    }


}

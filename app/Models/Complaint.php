<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Complaint extends Model
{
    use HasFactory;

    protected $table = 'complaints';
    protected $fillable = [
        'complaint_from','branch_id', 'subject', 'complaint_date', 'message', 'status', 'created_by', 'updated_by','image'
    ];


    const RECORDS_PER_PAGE = 20;
    const UPLOAD_PATH = 'uploads/complaint/';

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
    public function complainFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complaint_from', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function complaintEmployee()
    {
        return $this->hasMany(ComplaintEmployee::class,'complaint_id','id');
    }
    public function complaintDepartment()
    {
        return $this->hasMany(ComplaintDepartment::class,'complaint_id','id');
    }
    public function complaintReply()
    {
        return $this->hasMany(ComplaintResponse::class,'complaint_id','id');
    }
}

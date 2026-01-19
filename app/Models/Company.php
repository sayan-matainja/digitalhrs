<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Company extends Model
{
    use HasFactory;

    protected $table = 'companies';

    protected $fillable = [
        'name',
        'email',
        'owner_name',
        'address',
        'phone',
        'is_active',
        'website_url',
        'logo',
        'weekend',
        'count_holiday_weekend',
        'created_by',
        'updated_by'
    ];

    const RECORDS_PER_PAGE = 10;

    const UPLOAD_PATH = 'uploads/company/logo/';

//
//    public static function boot()
//    {
//        parent::boot();
//
//        static::updating(static function ($model) {
//            $model->updated_by = Auth::user()->id;
//        });
//    }

    protected function weekend(): Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value),
            set: fn($value) => json_encode($value),
        );
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by','id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class,'company_id','id')
            ->select('id','name')
            ->where('is_active',1);
    }

    public function employee()
    {
        return $this->hasMany(User::class,'company_id','id')
            ->select('*')
            ->where('is_active',1)
            ->where('status','verified')
            ->orderBy('name');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'name',
        'type_id',
        'image',
        'asset_code',
        'asset_serial_no',
        'is_working',
        'purchased_date',
        'warranty_available',
        'warranty_end_date',
        'is_available',
        'note',
        'created_by',
        'updated_by',
        'branch_id',
        'is_repaired',
    ];

    const IS_WORKING = ['yes','no'];

    const BOOLEAN_DATA = [
        0 => 'no',
        1 => 'yes'
    ];

    const RECORDS_PER_PAGE = 20;

    const UPLOAD_PATH = 'uploads/asset/';

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

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('branch', function ($query) use ($branchId) {
                    $query->where('id', $branchId);
                });
            });
        }
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class,'type_id','id');
    }


    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
    public function assignment(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id', 'id');
    }
    public function latestAssignment()
    {
        return $this->hasOne(AssetAssignment::class)
            ->latestOfMany('assigned_date');
    }





}

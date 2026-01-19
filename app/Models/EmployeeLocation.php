<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EmployeeLocation extends Model
{
    use HasFactory;
    protected $table = 'employee_locations';

    protected $fillable = [
        'employee_id','latitude','longitude', 'created_at','updated_at'
    ];

    const RECORDS_PER_PAGE = 20;
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('branch', function (Builder $builder) {

            $user = Auth::user();
            if (isset($user->branch_id) && (isset($user->id) && $user->id != 1)) {
                $branchId = $user->branch_id;
                $builder->whereHas('employee', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            }
        });
    }
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }

}

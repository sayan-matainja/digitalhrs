<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $table = 'attendance_logs';
    public $timestamps = true;
    const RECORDS_PER_PAGE = 20;
    protected $fillable = [
        'employee_id', 'attendance_type', 'identifier','created_at','updated_at'
    ];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('branch', function (Builder $builder) {

            $user = Auth::user();
            if (isset($user->branch_id) && (isset($user->id) && $user->id != 1)) {
                $branchId = $user->branch_id;
                $builder->whereHas('user', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });
            }
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}

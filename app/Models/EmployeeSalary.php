<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $table = 'employee_salaries';

    protected $fillable = ['employee_id','payroll_type','payment_type', 'annual_salary', 'basic_salary_type', 'basic_salary_value','monthly_hours', 'monthly_basic_salary', 'annual_basic_salary',
        'monthly_fixed_allowance', 'annual_fixed_allowance', 'salary_group_id','hour_rate','weekly_hours','weekly_basic_salary','weekly_fixed_allowance'];


    public static function boot()
    {
        parent::boot();

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
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}

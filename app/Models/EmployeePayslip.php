<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class EmployeePayslip extends Model
{
    use HasFactory;

    protected $table = 'employee_payslips';

    const UPLOAD_PATH = 'uploads/payslip/';

    public $timestamps = true;

    protected $casts = [
        'tada_ids' => 'array',
        'advance_salary_ids' => 'array',
    ];

    protected $fillable = [

        'employee_id',
        'paid_on',
        'status',
        'remark',
        'salary_cycle',
        'salary_from',
        'salary_to',
        'gross_salary',
        'tds',
        'advance_salary',
        'tada',
        'net_salary',
        'total_days',
        'present_days',
        'absent_days',
        'leave_days',
        'created_by',
        'updated_by',
        'payment_method_id',
        'include_tada',
        'include_advance_salary',
        'attendance',
        'absent_paid',
        'approved_paid_leaves',
        'absent_deduction',
        'holidays',
        'weekends',
        'paid_leave',
        'unpaid_leave',
        'overtime',
        'undertime',
        'created_at',
        'updated_at',
        'is_bs_enabled',
        'ssf_deduction',
        'ssf_contribution',
        'bonus',
        'tada_ids',
        'advance_salary_ids',
        'working_hours',
        'worked_hours',
        'overtime_hours',
        'undertime_hours',
        'hour_rate',
        'pf_deduction',
        'pf_contribution',
        'loan_amount',
        'loan_repayment_id'
    ];

    public static function boot()
    {
        parent::boot();

        if (Auth::check()  && isset(Auth::user()->branch_id)) {
            $branchId = Auth::user()->branch_id;

            static::addGlobalScope('branch', function (Builder $builder) use($branchId){
                $builder->whereHas('employee', function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                });

            });
        }
    }

    public function payslipDetail():HasMany
    {
        return $this->hasMany(EmployeePayslipDetail::class, 'employee_payslip_id', 'id');
    }

    public function additionalData():HasMany
    {
        return $this->hasMany(EmployeePayslipAdditionalDetail::class, 'employee_payslip_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');
    }
}

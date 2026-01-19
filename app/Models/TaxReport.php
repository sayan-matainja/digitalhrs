<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class TaxReport extends Model
{
    use HasFactory;

    protected $table = 'tax_reports';

    protected $fillable = [
        'employee_id', 'fiscal_year_id', 'total_basic_salary', 'total_allowance', 'total_ssf_contribution', 'total_ssf_deduction', 'female_discount',
        'other_discount', 'total_payable_tds', 'total_paid_tds', 'total_due_tds','months','total_pf_contribution', 'total_pf_deduction',
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

    public function fiscalYear():BelongsTo
    {
        return $this->belongsTo(FiscalYear::class, 'fiscal_year_id', 'id');
    }

    public function employee():BelongsTo
    {
        return $this->belongsTo(User::class,'employee_id','id');
    }

    public function additionalDetail(): HasMany
    {
        return $this->hasMany(TaxReportAdditionalDetail::class,'tax_report_id','id');
    }
    public function bonusDetail(): HasMany
    {
        return $this->hasMany(TaxReportBonusDetail::class,'tax_report_id','id');
    }
    public function tdsDetail(): HasMany
    {
        return $this->hasMany(TaxReportTdsDetail::class,'tax_report_id','id');
    }
    public function componentDetail(): HasMany
    {
        return $this->hasMany(TaxReportComponentDetail::class,'tax_report_id','id');
    }

    public function reportDetail(): HasMany
    {
        return $this->hasMany(TaxReportDetail::class,'tax_report_id','id');
    }
}

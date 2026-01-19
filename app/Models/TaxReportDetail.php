<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxReportDetail extends Model
{
    use HasFactory;
    protected $table = 'tax_report_details';

    protected $fillable = [
        'tax_report_id', 'month', 'salary', 'basic_salary','fixed_allowance','ssf_contribution','ssf_deduction','pf_deduction',
        'pf_contribution',
    ];

    public function taxReport():BelongsTo
    {
        return $this->belongsTo(TaxReport::class,'tax_report_id','id');
    }


}

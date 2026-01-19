<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxReportAdditionalDetail extends Model
{
    use HasFactory;

    protected $table = 'tax_report_additional_details';

    protected $fillable = [
        'tax_report_id', 'salary_component_id', 'amount','month'
    ];

    public function taxReport():BelongsTo
    {
        return $this->belongsTo(TaxReport::class,'tax_report_id','id');
    }
    public function salaryComponent():BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class,'salary_component_id','id');
    }

}

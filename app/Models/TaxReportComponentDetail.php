<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxReportComponentDetail extends Model
{
    use HasFactory;

    protected $table = 'tax_report_component_details';

    protected $fillable = [
        'tax_report_id', 'salary_component_id', 'type', 'month', 'amount','month'
    ];

    public function salaryComponent():BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class,'salary_component_id','id');
    }

    public function taxReport(): HasMany
    {
        return $this->hasMany(TaxReport::class,'tax_report_id','id');
    }
}

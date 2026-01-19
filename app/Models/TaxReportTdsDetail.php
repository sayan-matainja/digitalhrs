<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxReportTdsDetail extends Model
{
    use HasFactory;

    protected $table = 'tax_report_tds_details';

    protected $fillable = [
        'tax_report_id', 'month', 'amount', 'is_paid',
    ];

    public function taxReport():BelongsTo
    {
        return $this->belongsTo(TaxReport::class,'tax_report_id','id');
    }

}

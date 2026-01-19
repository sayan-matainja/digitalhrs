<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxReportBonusDetail extends Model
{
    use HasFactory;

    protected $table = 'tax_report_bonus_details';

    protected $fillable = [
        'tax_report_id', 'bonus_id', 'month', 'amount','tax'
    ];

    public function taxReport():BelongsTo
    {
        return $this->belongsTo(TaxReport::class,'tax_report_id','id');
    }

    public function bonus():BelongsTo
    {
        return $this->belongsTo(Bonus::class,'bonus_id','id');
    }

}

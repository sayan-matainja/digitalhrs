<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;

class PaymentCurrency extends Model
{
    use HasFactory;

    protected $table = 'payment_currencies';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'symbol',
    ];


}

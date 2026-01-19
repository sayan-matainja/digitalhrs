<?php

namespace App\Repositories;

use App\Models\PaymentCurrency;
use Illuminate\Support\Facades\Cache;

class PaymentCurrencyRepository
{
    public function findPayrollCurrency($select=['*'])
    {
        return PaymentCurrency::select($select)->first();
    }

    public function updateOrCreatePaymentCurrency($currencyDetail,$validatedData)
    {
        Cache::forget('payment_currency_symbol');
        if($currencyDetail){
            return $currencyDetail->update($validatedData);
        }
        return PaymentCurrency::create($validatedData);
    }
}

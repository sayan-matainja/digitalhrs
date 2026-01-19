<?php

namespace App\Http\Controllers\Web;




use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;


class EmiCalculatorController extends Controller
{

    private $view = 'admin.loanManagement.loan.';

    public function emiCalculator ()
    {
        $currency = AppHelper::getCompanyPaymentCurrencySymbol();

        return view($this->view . 'emi_calculator',compact('currency'));
    }



}

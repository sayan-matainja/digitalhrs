@extends('layouts.master')
@section('title',__('index.payment_currency'))
@section('sub_page',__('index.currency_setting'))
@section('page')
    <a href="{{ route('admin.payment-currency.index')}}">
        {{__('index.payment_currency')}}
    </a>
@endsection

@section('main-content')
    <section class="content">
        @include('admin.section.flash_message')

        <nav class="page-breadcrumb d-flex align-items-center justify-content-between">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
                <li class="breadcrumb-item"> {{ __('index.currency_setting') }}</li>
            </ol>
        </nav>
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample"  action="{{route('admin.payment-currency.save')}}" method="POST">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-lg-6 mb-4">
                            <label for="status" class="form-label">{{ __('index.payroll_currency') }} <span style="color: red">*</span> </label>
                            <select class="form-select" id="currency" name="currency"  >
                                <option value="" {{isset($currencyDetail) ? '' : 'selected'}}  disabled>{{ __('index.choose_payroll_currency') }}</option>
                                @foreach(\App\Helpers\PaymentCurrencyHelper::CURRENCY_DETAIL as $key => $value)
                                    <option value="{{$value['id']}}"
                                        {{ (isset($currencyDetail) && ($currencyDetail->code) == $value['code']) ? 'selected': '' }}>

                                        {{$value['symbol']}} ({{$value['name']}} - {{$value['code']}})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @can('payment_currency')
                        <div class="col-lg-6 mt-lg-4 mb-4 text-start">
                            <button type="submit" class="btn btn-primary">{{ __('index.submit') }}</button>
                        </div>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection


@section('scripts')

    <script>
        $(document).ready(function () {
            $("#currency").select2({
                placeholder: {{ __('index.choose_payroll_currency') }}
            });
        });
    </script>

@endsection









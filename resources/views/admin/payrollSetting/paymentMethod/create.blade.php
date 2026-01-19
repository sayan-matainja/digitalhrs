@extends('layouts.master')

@section('title',__('index.payment_method'))

@section('page')
    <a href="{{ route('admin.payment-methods.index')}}">
        {{ __('index.payment_method') }}
    </a>
@endsection

@section('sub_page','Create')

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')
        <div class="row">
            <div class="col-xl-2 col-lg-3 mb-4">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-xl-10 col-lg-9 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h4>{{ __('index.add_payment_method') }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="paymentMethodAdd" class="forms-sample " action="{{route('admin.payment-methods.store')}}"  method="POST">
                            @csrf
                            <div id="addPaymentMethod">
                                <div class="row paymentMethodList align-items-center justify-content-between mb-3">
                                    <div class="col-lg-7">
                                        <input type="text" class="form-control" id="name"  name="name[]" value="" required  placeholder=" {{ __('index.payment_method_name') }}">
                                    </div>
                                    <div class="col-lg-5 text-md-start text-center addButtonSection">
                                        <button type="button" class="btn btn-primary" id="add" title="Add more payment Method">
                                            {{__('index.add')}} </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success" id="paymentMethodSubmit">
                                {{__('index.submit')}} </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.payrollSetting.paymentMethod.common.scripts')
@endsection

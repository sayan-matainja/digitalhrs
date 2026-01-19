@extends('layouts.master')

@section('title',__('index.advance_salary'))

@section('action',__('index.view'))

@section('styles')
    <link rel="stylesheet" href="{{asset('assets/css/imageuploadify.min.css')}}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet" />
@endsection

@section('button')
    <a href="{{route('admin.advance-salaries.index')}}" >
        <button class="btn btn-sm btn-primary" ><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
    </a>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{asset('assets/css/imageuploadify.min.css')}}">
@endsection

<?php
    $status = [
        'pending' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
        'processing' => 'secondary'
    ];
?>

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payroll.advanceSalary.common.breadcrumb')

        <div class="row position-relative">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">{{ __('index.advance_salary_detail') }}</h4>
                    </div>

                    <div class="card-body">
                        {!!  $advanceSalaryDetail->description!!}
                        @if(isset($attachments) && count($attachments) > 0 )
                            <div class="salary-adv border-top pt-3 mt-3">
                                <h6 class="mb-3">{{ __('index.proof_of_advance_salary_payment') }} </h6>
                                <div class="row mb-4 mt-3">
                                    @foreach($attachments as $key => $data)
                                        @if(in_array(pathinfo(asset(\App\Models\AdvanceSalaryAttachment::UPLOAD_PATH.$data->name), PATHINFO_EXTENSION),['jpeg','png','jpg'])  )
                                            <div class="col-lg-3 mb-4">
                                                <div class="uploaded-image">
                                                    <a href="{{ asset(\App\Models\AdvanceSalaryAttachment::UPLOAD_PATH.$data->name) }}" data-lightbox="image-1" data-title="{{$data->name}}">
                                                        <img class="w-100" style=""
                                                             src="{{ asset(\App\Models\AdvanceSalaryAttachment::UPLOAD_PATH.$data->name) }}"
                                                             alt="document images">
                                                    </a>

                                                    <p>{{$data->name}}</p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="uploaded-files">
                                                <div class="row align-items-center">
                                                    <div class="col-lg-1">
                                                        <div class="file-icon">
                                                            <i class="link-icon" data-feather="file-text"></i>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-10">
                                                        <a target="_blank" href="{{asset(\App\Models\AdvanceSalaryAttachment::UPLOAD_PATH.$data->name)}}">
                                                            {{asset(\App\Models\AdvanceSalaryAttachment::UPLOAD_PATH.$data->name)}}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 sidebar-list position-relative">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>{{ __('index.advance_salary_request_detail') }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-border">
                            <tbody>
                            <tr>
                                <td>{{ __('index.employee') }}:</td>
                                <td class="text-end text-success">{{ucfirst($advanceSalaryDetail->requestedBy->name)}}</td>
                            </tr>

                            <tr>
                                <td>{{ __('index.total_request_amount') }}:</td>
                                <td class="text-end">
                                    {{\App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol()}}.{{number_format($advanceSalaryDetail->requested_amount)}}
                                </td>
                            </tr>

                            <tr>
                                <td>{{ __('index.status') }}:</td>
                                <td class="text-end">
                                        <span class="btn btn-{{$status[$advanceSalaryDetail->status]}} cursor-default btn-xs">
                                            {{ucfirst($advanceSalaryDetail->status)}}
                                        </span>
                                </td>
                            </tr>

                            <tr>
                                <td>{{ __('index.total_released_amount') }}:</td>
                                <td class="text-end">
                                    {{\App\Helpers\AppHelper::getCompanyPaymentCurrencySymbol()}}.{{number_format($advanceSalaryDetail->released_amount)}}
                                </td>
                            </tr>

                            <tr>
                                <td>{{ __('index.advance_requested_date') }}:</td>
                                <td class="text-end text-danger">{{ \App\Helpers\AppHelper::formatDateForView($advanceSalaryDetail->advance_requested_date)}}</td>
                            </tr>

                            <tr>
                                <td>{{ __('index.amount_released_date') }}:</td>
                                <td class="text-end text-danger">{{ $advanceSalaryDetail->amount_granted_date ? \App\Helpers\AppHelper::formatDateForView($advanceSalaryDetail->amount_granted_date)  : 'Not Yet Released'}}</td>
                            </tr>

                            <tr>
                                <td>{{ __('index.verified_by') }}:</td>
                                <td class="text-end text-success">
                                    {{$advanceSalaryDetail->verifiedBy ? ucfirst($advanceSalaryDetail->verifiedBy->name) : 'Admin'}}
                                </td>
                            </tr>

                            @if(isset( $advanceSalaryDetail->remark))
                            <tr>
                                <td>{{ __('index.remark') }}</td>
                                <td class="text-end">
                                    <span class="text-end text-muted"> {{ $advanceSalaryDetail->remark ?? ''}}</span>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td>{{ __('index.is_settled') }}</td>
                                <td class="text-end">
                                    <span class="text-end text-muted"> {{$advanceSalaryDetail->is_settled ? 'Yes': 'No'}}</span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if(!in_array($advanceSalaryDetail->status, ['approved','rejected']))
                @include('admin.payroll.advanceSalary.edit_form')
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    @include('admin.payroll.advanceSalary.common.scripts')
@endsection


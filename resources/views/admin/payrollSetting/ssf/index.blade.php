@extends('layouts.master')
@section('title',__('index.ssf'))
@section('page')
    <a href="{{ route('admin.ssf.index')}}">
        {{ __('index.ssf') }}
    </a>
@endsection

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
                        <h6 class="card-title mb-0">{{ __('index.ssf_rule') }}</h6>
                    </div>
                    <div class="card-body">
                            @if(!isset($ssfDetail) && empty($ssfDetail))
                                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                                      action="{{route('admin.ssf.store')}}">
                            @else
                                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                                      action="{{route('admin.ssf.update', $ssfDetail->id)}}">
                                    @method('PUT')
                            @endif

                                @csrf
                                @include('admin.payrollSetting.ssf.form')
                            </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection
@section('scripts')
    <script>
        $('document').ready(function(){


            $('.nepaliDate').nepaliDatePicker({
                language: "english",
                dateFormat: "MM/DD/YYYY",
                ndpYear: true,
                ndpMonth: true,
                ndpYearCount: 20,
                disableAfter: "2089-12-30",
            });

        });

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Feather Icons
            feather.replace();

            // Initialize Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

@endsection





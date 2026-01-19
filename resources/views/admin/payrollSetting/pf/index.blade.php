@extends('layouts.master')
@section('title',__('index.pf'))
@section('page')
    <a href="{{ route('admin.pf.index')}}">
        {{ __('index.pf') }}
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
                        <h6 class="card-title mb-0">{{ __('index.pf_rule') }}</h6>
                    </div>
                    <div class="card-body">
                            @if(!isset($pfDetail) && empty($pfDetail))
                                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                                      action="{{route('admin.pf.store')}}">
                            @else
                                <form class="forms-sample" enctype="multipart/form-data" method="POST"
                                      action="{{route('admin.pf.update', $pfDetail->id)}}">
                                    @method('PUT')
                            @endif

                                @csrf
                                @include('admin.payrollSetting.pf.form')
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

    </script>

@endsection





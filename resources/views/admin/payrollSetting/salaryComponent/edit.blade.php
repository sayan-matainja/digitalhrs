
@extends('layouts.master')

@section('title',__('index.salary_components'))

@section('page')
    <a href="{{ route('admin.salary-components.index')}}">
        {{ __('index.salary_components') }}
    </a>
@endsection

@section('sub_page','Edit')

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.payrollSetting.common.breadcrumb')
        <div class="row">
            <div class="col-lg-2">
                @include('admin.payrollSetting.common.setting_menu')
            </div>
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <form class="forms-sample" action="{{route('admin.salary-components.update',$salaryComponentDetail->id)}}"  method="POST">
                            @method('PUT')
                            @csrf
                            @include('admin.payrollSetting.salaryComponent.common.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </section>
@endsection

@section('scripts')
    @include('admin.payrollSetting.salaryComponent.common.scripts')
@endsection

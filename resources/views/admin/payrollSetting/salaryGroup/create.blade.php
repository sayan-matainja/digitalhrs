@extends('layouts.master')

@section('title',__('index.salary_group'))

@section('page')
    <a href="{{ route('admin.salary-groups.index')}}">
        {{ __('index.salary_group') }}
    </a>
@endsection

@section('sub_page',__('index.create'))

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
                    <div class="card-body">
                        <form class="forms-sample" action="{{route('admin.salary-groups.store')}}"  method="POST">
                            @csrf
                            @include('admin.payrollSetting.salaryGroup.common.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </section>
@endsection

@section('scripts')
    @include('admin.payrollSetting.salaryGroup.common.scripts')
@endsection

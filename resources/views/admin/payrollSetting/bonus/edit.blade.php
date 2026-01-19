
@extends('layouts.master')

@section('title',__('index.edit'))

@section('page')
    <a href="{{ route('admin.bonus.index')}}">
        {{ __('index.bonus') }}
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
                        <form class="forms-sample" action="{{route('admin.bonus.update',$bonusDetail->id)}}"  method="POST">
                            @method('PUT')
                            @csrf
                            @include('admin.payrollSetting.bonus.common.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </section>
@endsection

@section('scripts')
    @include('admin.payrollSetting.bonus.common.scripts')
@endsection

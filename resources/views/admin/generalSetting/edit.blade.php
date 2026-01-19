
@extends('layouts.master')

@section('title','Edit General Setting')

@section('action','Edit General Setting')

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.generalSetting.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form class="forms-sample" action="{{route('admin.general-settings.update',$generalSettingDetail->id)}}"  method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.generalSetting.common.form')
                </form>
            </div>
        </div>
    </section>
@endsection




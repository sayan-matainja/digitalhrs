@extends('layouts.master')

@section('title', __('index.edit_user_detail'))

@section('action', __('index.edit'))

@section('button')
    <div class="float-end">
        <a href="{{ route('admin.employees.index') }}">
            <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.employees.common.breadcrumb')

        <div class="card-user">
            <form class="forms-sample" id="employeeDetail" action="{{ route('admin.employees.update', $userDetail->id) }}" enctype="multipart/form-data" method="post">
                @method('PUT')
                @csrf
                @include('admin.employees.common.form')
            </form>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.employees.common.scripts')

@endsection

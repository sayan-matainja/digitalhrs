@extends('layouts.master')

@section('title', __('index.create_employee'))

@section('action', __('index.add'))

@section('button')
    <div class="float-md-end">
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
            <form class="forms-sample" id="employeeDetail" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data" method="POST">
                @csrf
                @include('admin.employees.common.form')
            </form>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.employees.common.scripts')

@endsection

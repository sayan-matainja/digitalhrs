@extends('layouts.master')

@section('title', __('index.users'))

@section('action', __('index.add'))

@section('button')
    <div class="float-end">
        <a href="{{ route('admin.users.index') }}">
            <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.back') }}</button>
        </a>
    </div>
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.users.common.breadcrumb')

        <div class="card-user">
            <form class="forms-sample" id="employeeDetail" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" method="POST">
                @csrf
                @include('admin.users.common.form')
            </form>
        </div>

    </section>
@endsection

@section('scripts')

    @include('admin.users.common.scripts')

@endsection

@extends('layouts.master')

@section('title', __('index.branch_create'))

@section('button')

    <a href="{{ route('admin.branch.index') }}">
        <button class="btn btn-sm btn-primary"><i class="link-icon" data-feather="arrow-left"></i> {{ __('index.button_back') }}</button>
    </a>
@endsection

@section('main-content')

    <section class="content">
        @include('admin.section.flash_message')
        @include('admin.branch.common.breadcrumb', ['title' => __('index.create')])
        <div class="card">
            <div class="card-body pb-0">
                <form class="forms-sample" action="{{ route('admin.branch.store') }}" enctype="multipart/form-data" method="POST">
                    @csrf
                    @include('admin.branch.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

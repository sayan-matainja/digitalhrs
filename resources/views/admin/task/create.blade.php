@extends('layouts.master')

@section('title', __('index.create_task'))

@section('action', __('index.create_task'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/imageuploadify.min.css') }}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.task.common.breadcrumb')

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form id="taskAdd" class="forms-sample" action="{{ route('admin.tasks.store') }}" enctype="multipart/form-data" method="POST">
                            @csrf
                            @include('admin.task.common.form')
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.task.common.scripts')
@endsection

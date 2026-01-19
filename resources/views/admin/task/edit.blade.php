@extends('layouts.master')

@section('title', __('index.edit_task'))

@section('action', __('index.edit_task_detail'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/imageuploadify.min.css') }}">
@endsection

@section('main-content')

    <section class="content">

        @include('admin.section.flash_message')

        @include('admin.task.common.breadcrumb')

        <div class="card">
            <div class="card-body">
                <form id="projectEdit" class="forms-sample" action="{{ route('admin.tasks.update', $taskDetail->id) }}" enctype="multipart/form-data" method="post">
                    @method('PUT')
                    @csrf
                    @include('admin.task.common.form')
                </form>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    @include('admin.task.common.scripts')
@endsection

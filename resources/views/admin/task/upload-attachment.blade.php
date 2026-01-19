@extends('layouts.master')

@section('title', __('index.task_attachment_title'))

@section('action', __('index.upload_attachment_action'))

@section('button')
    <div class="float-end">
        <a href="{{ route('admin.tasks.show', $taskId) }}">
            <button class="btn btn-sm btn-primary">
                <i class="link-icon" data-feather="arrow-left"></i>
                {{ __('index.back') }}
            </button>
        </a>
    </div>
@endsection

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
                    <div class="card-header">
                        <h5 class="text-muted">{{ __('index.upload_task_attachments_text') }}</h5>
                    </div>
                    <div class="card-body">
                        <form id="taskAttachment" class="forms-sample" action="{{ route('admin.task-attachment.store') }}"
                              enctype="multipart/form-data"
                              method="POST"
                        >
                            @csrf
                            <div class="row">
                                <input type="hidden" value="{{ $taskId }}" readonly name="task_id">
                                <div class="mb-3 col-12">
                                    <div>
                                        <input id="image-uploadify" type="file" name="attachments[]"
                                               accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip" multiple />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">{{ __('index.submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/imageuploadify.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            $("#image-uploadify").imageuploadify();
        });
    </script>
@endsection

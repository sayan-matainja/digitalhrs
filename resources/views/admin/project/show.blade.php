@extends('layouts.master')

@section('title', __('index.show_project_detail'))
@section('action', __('index.detail'))

@section('styles')
    <style>
        #updateMemberModal .modal-content {
            overflow: visible !important;
        }

        #updateMemberModal .modal-body {
            overflow: visible !important;
        }
        .select2-container--open {
            z-index: 9999; /* Higher than Bootstrap modal's z-index (usually 1050) */
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/imageuploadify.min.css') }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet" />
@endsection

@section('button')
    <div class="float-md-end d-md-flex align-items-center mb-4">
        @can('edit_project')
            <a href="{{ route('admin.projects.edit', $projectDetail->id) }}" >
                <button class="btn btn-success me-md-2 d-md-flex align-items-center">
                    <i class="link-icon me-2" data-feather="edit"></i> @lang('index.edit_project')
                </button>
            </a>
        @endcan

        @can('create_task')
            <a href="{{ route('admin.project-task.create', $projectDetail->id) }}" >
                <button class="btn btn-secondary me-md-2 d-md-flex align-items-center">
                    <i class="link-icon me-2" data-feather="plus"></i> @lang('index.create_task')
                </button>
            </a>
        @endcan

        @can('upload_project_attachment')
                <a href="#" data-bs-toggle="modal" data-bs-target="#projectAttachmentModal">
                    <button class="btn btn-primary d-md-flex align-items-center">
                        <i class="link-icon me-2" data-feather="clipboard"></i> @lang('index.upload_attachments')
                    </button>
                </a>
        @endcan
    </div>
@endsection

@section('main-content')

    <section class="content pb-0">

        @include('admin.section.flash_message')

        @include('admin.project.common.breadcrumb')

        <div class="row position-relative">
                <?php
                $ProjectStatus = [
                    'in_progress' => 'primary',
                    'not_started' => 'primary',
                    'on_hold' => 'info',
                    'cancelled' => 'danger',
                    'completed' => 'success',
                ]
                ?>
            <div class="col-lg-8 mb-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="mb-2">{{ ucfirst($projectDetail->name) }}</h3>
                        <ul class="list-unstyled d-flex mb-0">
                            <li class="text-muted me-2">@lang('index.total_tasks'): {{ $projectDetail->tasks->count() }}</li>
                            <li class="text-muted">@lang('index.completed_tasks'): {{ $projectDetail->completedTask ? $projectDetail->completedTask->count() : 0 }}</li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="progress mb-3">
                            <div class="progress-bar color2" role="progressbar"
                                 style="{{ \App\Helpers\AppHelper::getProgressBarStyle($projectDetail->getProjectProgressInPercentage()) }}"
                                 aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                <span>{{ $projectDetail->getProjectProgressInPercentage() }} %</span>
                            </div>
                        </div>
                        {!! $projectDetail->description !!}
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">@lang('index.uploaded_image_files')</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($images as $key => $imageData)
                                <div class="col-lg-3 col-md-6 mb-4">
                                    <div class="uploaded-image">
                                        <a href="{{ asset(\App\Models\Attachment::UPLOAD_PATH.$imageData->attachment) }}" data-lightbox="image-1" data-title="{{ $imageData->attachment }}">
                                            <img class="w-100" style=""
                                                 src="{{ asset(\App\Models\Attachment::UPLOAD_PATH.$imageData->attachment) }}"
                                                 alt="@lang('index.document_images')">
                                        </a>
                                        <p>{{ $imageData->attachment }}</p>

                                        @can('delete_pm_attachment')
                                            <a class="documentDelete" data-href="{{ route('admin.attachment.delete', $imageData->id) }}">
                                                <i class="link-icon remove-image" data-feather="x"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted">@lang('index.no_project_image_found')</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">@lang('index.uploaded_files')</h5>
                    </div>
                    <div class="card-body">
                        @forelse($files as $key => $fileData)
                            <div class="uploaded-files">
                                <div class="row align-items-center">
                                    <div class="col-lg-1">
                                        <div class="file-icon">
                                            <i class="link-icon" data-feather="file-text"></i>
                                        </div>
                                    </div>
                                    <div class="col-lg-10">
                                        <a target="_blank" href="{{ asset(\App\Models\Attachment::UPLOAD_PATH.$fileData->attachment) }}">
                                            {{ asset(\App\Models\Attachment::UPLOAD_PATH.$fileData->attachment) }}
                                        </a>
                                        <p>{{ date_format($fileData->created_at, 'M d Y') }}</p>
                                    </div>

                                    @can('delete_pm_attachment')
                                        <div class="col-lg-1">
                                            <a class="documentDelete" data-href="{{ route('admin.attachment.delete', $fileData->id) }}">
                                                <i class="link-icon remove-files" data-feather="x"></i>
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">@lang('index.no_project_files_found')</p>
                        @endforelse
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>@lang('index.project_tasks_lists')</h5>
                    </div>
                    <div class="card-body">
                        @forelse($projectDetail->tasks as $key => $value)
                            <div class="row title-section align-items-center mb-2 border-bottom">
                                <div class="col-lg-7 col-md-7 mb-2">
                                    <span class="pe-2">{{ ++$key }}.</span><a href="{{ route('admin.tasks.show', $value->id) }}">{{ ucfirst($value->name) }}</a>
                                </div>
                                <div class="col-lg-5 col-md-5 d-flex justify-content-between position-relative mb-2">
                                    <div class="assigned_members d-flex align-items-center gap-1">
                                        @forelse($value->assignedMembers as $key => $memberDetail)
                                            <img class="rounded-circle checklist-image" style="object-fit: cover"
                                                src="{{ isset($memberDetail->user->avatar) ? asset(\App\Models\User::AVATAR_UPLOAD_PATH.$memberDetail->user->avatar) : asset('assets/images/img.png') }}"
                                                alt="@lang('index.profile')">
                                        @empty
                                        @endforelse
                                    </div>


                                    @canany(['edit_task','show_task_detail','delete_task'])

                                    <div class="btn-group card-option position-absolute end-0 top-50 translate-middle">
                                        <button type="button" class="btn dropdown-toggle p-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="link-icon"  data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            @can('edit_task')
                                                <a href="{{ route('admin.tasks.edit', $value->id) }}" class="d-block py-1">
                                                    <i class="link-icon me-2" data-feather="edit"></i> @lang('index.edit')
                                                </a>
                                            @endcan

                                            @can('show_task_detail')
                                                <a href="{{ route('admin.tasks.show', $value->id) }}" class="d-block py-1">
                                                    <i class="link-icon me-2" data-feather="eye"></i> @lang('index.view')
                                                </a>
                                            @endcan

                                            @can('delete_task')
                                                <a data-href="{{ route('admin.tasks.delete', $value->id) }}" class="delete d-block py-1">
                                                    <i class="link-icon me-2"  data-feather="delete"></i> @lang('index.delete')
                                                </a>
                                            @endcan
                                        </div>
                                    </div>

                                    @endcanany
                                </div>
                            </div>
                        @empty
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4 sidebar-list position-relative">
                <div class="position-sticky top-0">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>@lang('index.project_summary')</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-border">
                                <tbody>
                                <tr>
                                    <td>@lang('index.cost'):</td>
                                    <td class="text-end">{{ $projectDetail->cost }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('index.total_hours'):</td>
                                    <td class="text-end">{{ $projectDetail->estimated_hours }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('index.created'):</td>
                                    <td class="text-end text-success">{{ \App\Helpers\AppHelper::formatDateForView($projectDetail->start_date) }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('index.deadline'):</td>
                                    <td class="text-end text-danger">{{ \App\Helpers\AppHelper::formatDateForView($projectDetail->deadline) }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('index.priority'):</td>
                                    <td class="text-end">
                                        <span class="btn btn-secondary btn-xs cursor-default">{{ ucfirst($projectDetail->priority) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>@lang('index.duration'):</td>
                                    <td class="text-end">
                                        <span class="badge badge-soft-success text-end d-inline-block float-end">
                                            {{ $projectDetail->projectRemainingDaysToComplete() }} @lang('index.days')
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>@lang('index.status'):</td>
                                    <td class="text-end">
                                        <span class="btn btn-{{ $ProjectStatus[$projectDetail->status] }} cursor-default btn-xs">
                                            {{ \App\Helpers\PMHelper::STATUS[$projectDetail->status] }}
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex w-100 align-items-center justify-content-between">
                            <h5>@lang('index.project_leaders')</h5>
                            <a class="btn btn-primary btn-sm open-employee-modal"
                               href="#"
                               data-add-type="leader"
                               data-href="{{ route('admin.projects.update-leader-data') }}"
                               data-project-id="{{ $projectDetail->id }}">
                                @lang('index.update_leader')
                            </a>
                        </div>
                        <div class="card-body pb-1">
                            @foreach($projectLeader as $key => $value)
                                <div class="member-section-inner d-flex align-items-center mb-3">
                                    <div class="member-section-image me-2">
                                        <img class="rounded-circle" style="object-fit: cover"
                                             src="{{ isset($value->user->avatar) ?
                                                asset(\App\Models\User::AVATAR_UPLOAD_PATH.$value->user->avatar) :
                                                asset('assets/images/img.png') }}"
                                             alt="@lang('index.profile')">
                                    </div>
                                    <div class="member-section-heading">
                                        <h5>{{ $value->user->name }}</h5>
                                        <p class="small text-muted">
                                            {{ $value->user->post ? $value->user->post->post_name : __('index.n_a') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header d-flex w-100 align-items-center justify-content-between">
                            <h5>@lang('index.project_members')</h5>
                            <a class="btn btn-primary btn-sm open-employee-modal"
                               href="#"
                               data-add-type="member"
                               data-href="{{ route('admin.projects.update-member-data') }}"
                               data-project-id="{{ $projectDetail->id }}">
                                @lang('index.update_member')
                            </a>
                        </div>
                        <div class="card-body pb-1">
                            @foreach($assignedMember as $key => $value)
                                <div class="member-section-inner d-flex align-items-center mb-3">
                                    <div class="member-section-image me-2">
                                        <img class="rounded-circle" style="object-fit: cover"
                                             src="{{ isset($value->user->avatar) ?
                                                asset(\App\Models\User::AVATAR_UPLOAD_PATH.$value->user->avatar) :
                                                asset('assets/images/img.png') }}"
                                             alt="@lang('index.profile')">
                                    </div>
                                    <div class="member-section-heading">
                                        <h5>{{ $value->user->name }}</h5>
                                        <p class="small text-muted">
                                            {{ $value->user->post ? $value->user->post->post_name : __('index.n_a') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>@lang('index.project_client_detail')</h5>
                        </div>
                        <div class="card-body">
                            <div class="member-section-inner d-flex align-items-center mb-3">
                                <div class="member-section-image me-2">
                                    <a href="{{ ($projectDetail->client && $projectDetail->client->id) ? route('admin.clients.show', $projectDetail->client->id) : '#' }}">
                                        <img class="rounded-circle" style="object-fit: cover"
                                             src="{{ asset(\App\Models\Client::UPLOAD_PATH . ($projectDetail->client ? $projectDetail->client->avatar : '')) }}"
                                             width="100" height="100"
                                             alt="">
                                    </a>
                                </div>
                                <div class="member-section-heading">
                                    <h5>{{ $projectDetail->client ? $projectDetail->client->name : '' }}</h5>
                                    <small>{{ $projectDetail->client ? $projectDetail->client->email : '' }}</small>
                                </div>
                            </div>
                            <table class="table table-striped table-border">
                                <tbody>
                                <tr>
                                    <td>@lang('index.phone_no'):</td>
                                    <td class="text-end">{{ $projectDetail->client ? $projectDetail->client->contact_no : '' }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('index.address'):</td>
                                    <td class="text-end">{{ $projectDetail->client ? $projectDetail->client->address : __('index.n_a') }}</td>
                                </tr>
                                <tr>
                                    <td>@lang('index.country'):</td>
                                    <td class="text-end">
                                        {{ $projectDetail->client ? $projectDetail->client->country : __('index.n_a') }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for uploading project attachments -->
    <div class="modal fade" id="projectAttachmentModal" tabindex="-1" aria-labelledby="projectAttachmentModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title"
                        id="projectAttachmentModalLabel">@lang('index.upload_project_attachments')</h5>
                </div>
                <div class="modal-body">
                    <form id="projectDocument" class="forms-sample"
                          action="{{ route('admin.project-attachment.store') }}" enctype="multipart/form-data"
                          method="POST">
                        @csrf
                        <input type="hidden" value="{{ $projectDetail->id }}" readonly name="project_id">
                        <div class="mb-3">
                            <input id="image-uploadify" type="file" name="attachments[]"
                                   accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip" multiple/>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">@lang('index.submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('admin.project.common.update-member')
    @include('admin.project.common.update-leader')
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/imageuploadify.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $("#image-uploadify").imageuploadify();
        });

        $(document).ready(function() {


            $('#updateMemberModal').on('shown.bs.modal', function() {

                $('#memberAdd').select2({
                    placeholder: 'Select employees',
                    dropdownParent: $('#updateMemberModal .modal-content')
                });

            });


            $('#updateLeaderModal').on('shown.bs.modal', function() {
                $('#leaderAdd').select2({
                    placeholder: 'Select employees',
                    dropdownParent: $('#updateLeaderModal .modal-content')
                });
            });



            $('.open-employee-modal').on('click', function(e) {
                e.preventDefault();
                const addType = $(this).data('add-type');
                const projectId = $(this).data('project-id');
                const actionUrl = $(this).data('href');
                const modalId = addType === 'leader' ? '#updateLeaderModal' : '#updateMemberModal';
                const formId = addType === 'leader' ? '#addLeaderToProjectForm' : '#addMemberToProjectForm';


                $(`${formId}`).attr('action', actionUrl);
                $(`${modalId} #${addType}_project_id`).val(projectId);


                $(modalId).modal('show');
            });


            $('#addMemberToProjectForm, #addLeaderToProjectForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const actionUrl = form.attr('action');
                const modal = form.closest('.modal');
                const formData = new FormData(this);


                const submitBtn = form.find('.submit');
                submitBtn.prop('disabled', true);

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        submitBtn.prop('disabled', false);

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                modal.modal('hide');
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Something went wrong!',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        submitBtn.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred!',
                            confirmButtonText: 'OK'
                        });
                        console.error('Error:', error);
                    });
            });
        });
    </script>
    @include('admin.project.common.scripts')

@endsection

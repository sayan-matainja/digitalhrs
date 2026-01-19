@extends('layouts.master')

@section('title', __('index.show_task_detail'))

@section('action', __('index.detail'))

@section("styles")
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet" />
@endsection

@section('button')
    <div class="breadcrumb-button float-md-end d-md-flex align-items-center">

        @can('edit_task')
            <a href="{{ route('admin.tasks.edit', $taskDetail->id) }}">
                <button class="btn btn-success d-md-flex align-items-center me-2">
                    <i class="link-icon me-1" data-feather="edit"></i>{{ __('index.task_edit') }}
                </button>
            </a>
        @endcan

        @can('create_checklist')
            <button class="btn btn-secondary d-md-flex align-items-center me-2 checklistAdd">
                <i class="link-icon me-1" data-feather="plus"></i>{{ __('index.create_checklist') }}
            </button>
        @endcan

        @can('upload_task_attachment')
            <a href="{{ route('admin.task-attachment.create', $taskDetail->id) }}">
                <button class="btn btn-primary d-md-flex align-items-center">
                    <i class="link-icon me-1" data-feather="clipboard"></i>{{ __('index.upload_attachment') }}
                </button>
            </a>
        @endcan

    </div>
@endsection

@section('main-content')

    <section class="content">
            <?php
            $status = [
                'in_progress' => 'primary',
                'not_started' => 'primary',
                'on_hold' => 'info',
                'cancelled' => 'danger',
                'completed' => 'success',
                'not_completed'=>'warning'
            ];
            ?>

        @include('admin.section.flash_message')

        @include('admin.task.common.breadcrumb')

        <div class="row position-relative">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="mb-2">{{ ucfirst($taskDetail->name) }}</h3>
                        <ul class="list-unstyled d-flex mb-0">
                            <li class="text-muted me-2">{{ __('index.total_checklist') }} : {{ $taskDetail->taskChecklists->count() }}</li>
                            <li class="text-muted">{{ __('index.completed_checklist') }}: {{ $taskDetail->completedTaskChecklist->count() }}</li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="progress mb-3">
                            <div class="progress-bar color2" role="progressbar"
                                 style="{{ \App\Helpers\AppHelper::getProgressBarStyle($taskDetail->taskChecklists->count() > 0 ? $taskDetail->getTaskProgressInPercentage() : (($taskDetail->status === 'completed') ? 100 : 0)) }}"
                                 aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                                <span>{{ $taskDetail->taskChecklists->count() > 0 ? $taskDetail->getTaskProgressInPercentage() : (($taskDetail->status === 'completed') ? 100 : 0) }} %</span>
                            </div>
                        </div>
                        {!! $taskDetail->description !!}

                        <div class="attachment">
                            <div class="row">
                                @forelse($images as $key => $imageData)
                                    <div class="col-lg-3 mb-4">
                                        <div class="uploaded-image">
                                            <a href="{{ asset(\App\Models\Attachment::UPLOAD_PATH . $imageData->attachment) }}" data-lightbox="image-1" data-title="{{ $imageData->attachment }}">
                                                <img class="w-100"
                                                     src="{{ asset(\App\Models\Attachment::UPLOAD_PATH . $imageData->attachment) }}"
                                                     alt="document images">
                                            </a>
                                            <p>{{ $imageData->attachment }}</p>

                                            @can('delete_pm_attachment')
                                                <a class="documentDelete" id="delete" data-title="{{ __('index.image') }}" href="{{ route('admin.attachment.delete', $imageData->id) }}">
                                                    <i class="link-icon remove-image" data-feather="x"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                @empty

                                @endforelse
                            </div>

                            <div class="row">
                                @forelse($files as $key => $fileData)
                                    <div class="uploaded-files">
                                        <div class="row align-items-center">
                                            <div class="col-lg-1">
                                                <div class="file-icon">
                                                    <i class="link-icon" data-feather="file-text"></i>
                                                </div>
                                            </div>
                                            <div class="col-lg-10">
                                                <a target="_blank" href="{{ asset(\App\Models\Attachment::UPLOAD_PATH . $fileData->attachment) }}">
                                                    {{ asset(\App\Models\Attachment::UPLOAD_PATH . $fileData->attachment) }}
                                                </a>
                                                <p>{{ \App\Helpers\AppHelper::formatDateForView($fileData->created_at) }}</p>
                                            </div>

                                            @can('delete_pm_attachment')
                                                <div class="col-lg-1">
                                                    <a class="documentDelete" id="delete" data-title="{{ __('index.file') }}" data-href="{{ route('admin.attachment.delete', $fileData->id) }}">
                                                        <i class="link-icon remove-files" data-feather="x"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                @empty

                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4 ">
                    <div class="card-body">
                        @include('admin.task.comment_section')
                    </div>
                </div>

                <div class="card checklistTaskAdd mb-4">
                    <div class="card-header">
                        <h5>{{ __('index.task_checklist_lists') }}</h5>
                    </div>
                    <div class="card-body pb-0">
                        @forelse($taskDetail->taskChecklists as $key => $value)
                            <div class="row align-items-center mb-4 border-bottom pb-2">
                                <div class="col-lg-8 col-md-8 d-flex align-items-center mb-2" style="{{ $value->is_completed ? 'text-decoration: line-through' : '' }}">
                                    @can('edit_checklist')
                                        <input type="checkbox" id="checklistToggle" class="me-2 align-middle" name="checklist" value="0" {{ $value->is_completed ? 'checked' : '' }}
                                        data-href="{{ route('admin.task-checklists.toggle-status', $value->id) }}" />
                                    @endcan
                                    {{ $value->name }}
                                </div>

                                <div class="col-lg-4 col-md-4 d-flex align-items-center justify-content-between mb-2">
                                    <div class="assigned-member">
                                        <p class="mb-1">{{ __('index.assigned_to') }}</p>
                                        <img class="rounded-circle checklist-image" style="object-fit: cover" title="{{ $value->taskAssigned->name }}"
                                         src="{{ $value->taskAssigned->avatar ? asset(\App\Models\User::AVATAR_UPLOAD_PATH . $value->taskAssigned->avatar) : asset('assets/images/img.png') }}"
                                         alt="profile">
                                    </div>
                                    <div class="text-end">
                                        @can('edit_checklist')
                                            <a href="{{ route('admin.task-checklists.edit', $value->id) }}" title="{{ __('index.edit_checklist') }}">
                                                <i class="link-icon" data-feather="edit"></i>
                                            </a>
                                        @endcan

                                        @can('delete_checklist')
                                            <a href="{{ route('admin.task-checklists.delete', $value->id) }}" data-title="{{ __('index.checklist') }}" id="delete" title="{{ __('index.delete_checklist') }}">
                                                <i class="link-icon" data-feather="delete"></i>
                                            </a>
                                        @endcan
                                    </div>
                                </div>

                            </div>
                        @empty

                        @endforelse

                        @can('create_checklist')
                            <div class="checklistForm">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-lg-7 col-md-6 mb-4">
                                        <h5>{{ __('index.create_task_checklist') }}</h5>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-4">
                                        <button class="btn btn-default btn-secondary float-md-end" id="createChecklist">{{ __('index.create_checklist') }}</button>
                                    </div>
                                </div>
                                <div class="formChecklist d-none">
                                    <form id="taskAdd" class="forms-sample" action="{{ route('admin.task-checklists.store') }}" method="POST">
                                        @csrf
                                        <div id="addTaskCheckList">
                                            <input type="hidden" name="task_id" id="taskId" value="{{ $taskDetail->id }}" />
                                            <div class="row checklist align-items-center justify-content-between">
                                                <div class="col-lg-7 col-md-7 mb-4">
                                                    <input type="text" class="form-control" id="name" name="name[]" value="" required placeholder="{{ __('index.enter_checklist_title') }}">
                                                </div>
                                                <div class="col-lg-3 col-md-3 mb-4">
                                                    <select class="form-select" id="assigned_to" name="assigned_to[]" required>
                                                        <option value="" {{ old('assigned_to') ? '' : 'selected' }} disabled>{{ __('index.select_member') }}</option>
                                                        @foreach($taskDetail->assignedMembers as $key => $value)
                                                            <option value="{{ $value->user->id }}" {{ (old('assigned_to') == $value->user->id) ? 'selected' : '' }}>
                                                                {{ $value->user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-2 col-md-2 mb-4 addButtonSection float-md-end">
                                                    <button type="button" class="btn btn-xs btn-primary" id="addChecklist" title="{{ __('index.add_more_checklist') }}"> + </button>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-success mb-4" id="checklistSubmit">{{ __('index.submit') }}</button>
                                    </form>
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

            @include('admin.task.task_summary')
        </div>
    </section>

    @include('admin.task.update-employee')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.open-employee-modal').on('click', function(e) {
                e.preventDefault();
                const projectId = $(this).data('task-id');
                const actionUrl = $(this).data('href');
                const modalId = '#updateMemberModal';
                const formId = '#updateTaskMemberForm';




                $(`${formId}`).attr('action', actionUrl);
                $(`${modalId} #member_task_id`).val(projectId);

                $(modalId).modal('show');
            });

            $('#updateMemberModal').on('shown.bs.modal', function() {
                $('#memberAdd').select2({
                    placeholder: 'Select employees',
                    dropdownParent: $('#updateMemberModal .modal-content') // Append dropdown to modal-content
                });
            });



            $('#updateTaskMemberForm').on('submit', function(e) {
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

    @include('admin.task.common.comment_scripts')

    @include('admin.task.common.scripts')

@endsection

<div class="row">

    @if(isset($project))
        <div class="col-lg-6 col-md-6 mb-4">
            <label for="project" class="form-label">@lang('index.project') <span style="color: red">*</span> </label>
            <select class="form-select form-select" name="project_id"  >
                <option value="{{$project->id}}" selected >{{ucfirst($project->name)}}</option>
            </select>
        </div>
    @else
        @if(!isset(auth()->user()->branch_id))
            <div class="col-lg-6 col-md-6 mb-4">
                <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
                <select class="form-select" id="branch_id" name="branch_id">
                    <option  {{!isset($taskDetail) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                    </option>
                    @if(isset($companyDetail))
                        @foreach($companyDetail->branches()->get() as $key => $branch)
                            <option value="{{$branch->id}}"
                                {{ (isset($taskDetail) && $taskDetail->branch_id  == $branch->id) ? 'selected': '' }}>
                                {{ucfirst($branch->name)}}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        @endif
        <div class="col-lg-6 col-md-6 mb-4">
            <label for="project" class="form-label">@lang('index.project') <span style="color: red">*</span> </label>
            <select class="form-select form-select" id="project" name="project_id"  >

            </select>
        </div>
    @endif

        <div class="col-lg-6 col-md-6 mb-4">
            <label for="title" class="form-label">@lang('index.task_name') <span style="color: red">*</span></label>
            <input type="text" class="form-control" id="name" name="name" required value="{{ ( isset($taskDetail) ?  $taskDetail->name: old('name') )}}"
                   autocomplete="off" placeholder="@lang('index.enter_task_name')">
        </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="start_date" class="form-label">@lang('index.task_start_date') <span style="color: red">*</span> </label>
        @if($isBsEnabled)
            <input type="text" class="form-control startNpDate" id="start_date" name="start_date" required value="{{ ( isset( $taskDetail) ?  \App\Helpers\AppHelper::taskDate($taskDetail->start_date): old('start_date') )}}"
                   autocomplete="off" >
        @else
            <input type="datetime-local" class="form-control" id="start_date" name="start_date" required value="{{ ( isset( $taskDetail) ?  $taskDetail->start_date: old('start_date') )}}"
                   autocomplete="off" >
        @endif

    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <label for="end_date" class="form-label">@lang('index.task_end_date') <span style="color: red">*</span> </label>
        @if($isBsEnabled)
            <input type="text" class="form-control npDeadline" id="end_date" name="end_date" required value="{{ ( isset( $taskDetail) ?  \App\Helpers\AppHelper::taskDate($taskDetail->end_date): old('end_date') )}}"
                   autocomplete="off" >
        @else
            <input type="datetime-local" class="form-control" id="end_date" name="end_date" required value="{{ ( isset( $taskDetail) ?  $taskDetail->end_date: old('end_date') )}}"
                   autocomplete="off" >
        @endif


    </div>
    @if($isBsEnabled)
        <div class="col-lg-3 col-md-6 mb-4">
            <label for="start_time" class="form-label">@lang('index.task_start_time') <span style="color: red">*</span> </label>
            <input type="time" class="form-control" id="start_time" name="start_time" required value="{{ ( isset( $taskDetail) ? date('H:i:s', strtotime($taskDetail->start_date)) : '' )}}"
                   autocomplete="off" >
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <label for="end_time" class="form-label">@lang('index.task_end_time') <span style="color: red">*</span> </label>
            <input type="time" class="form-control" id="end_time" name="end_time" required value="{{ ( isset( $taskDetail) ? date('H:i:s', strtotime($taskDetail->end_date)) : '' )}}"
                   autocomplete="off" >
        </div>
    @endif
    <div class="col-lg-3 col-md-6 mb-4">
        <label for="priority" class="form-label">@lang('index.priority')</label>
        <select class="form-select" id="priority" name="priority"  >
            <option value="" {{isset($taskDetail) ? '' : 'selected'}}  disabled>@lang('index.select_priority')</option>
            @foreach(\App\Models\Task::PRIORITY as $value)
                <option value="{{$value}}" {{ (isset($taskDetail) && ($taskDetail->priority ) == $value) || ( old('priority') == $value) ? 'selected': '' }}>
                    {{ucfirst($value)}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="status" class="form-label">@lang('index.task_status') </label>
        <select class="form-select" id="status" name="status"  >
            <option value="" {{isset($taskDetail) ? '' : 'selected'}}  disabled>@lang('index.select_task_status')</option>
            @foreach(\App\Models\Task::STATUS as $value)
                <option value="{{$value}}" {{ (isset($taskDetail) && ($taskDetail->status ) == $value) || (old('status') == $value) ? 'selected': '' }}>
                    {{\App\Helpers\PMHelper::STATUS[$value]}}</option>
            @endforeach
        </select>
    </div>

    @if(isset($projectMember))
        <div class=" col-lg-6 mb-4">
            <label for="assignedMember" class="form-label">@lang('index.assign_member') <span style="color: red">*</span></label>
            <br>
            <select class="col-md-12 form-select" id="assignedMember" name="assigned_member[]" multiple="multiple" required>

                @foreach($projectMember as $key => $datum)
                    <option value="{{$datum->user->id}}"  >{{ucfirst($datum->user->name)}}</option>
                @endforeach

            </select>
        </div>
    @else
        <div class=" col-lg-6 mb-4 taskMemberAssignDiv">
            <label for="taskMember" class="form-label">@lang('index.assign_member') <span style="color: red">*</span></label>
            <br>
            <select class="col-md-12 form-select" id="taskMember" name="assigned_member[]" multiple="multiple" required>

            </select>
        </div>
    @endif

    <div class="col-lg-6 mb-4">
        <label for="description" class="form-label">@lang('index.description')<span style="color: red">*</span></label>
        <textarea class="form-control" name="description" id="tinymceExample" rows="4">{{ ( isset($taskDetail) ? $taskDetail->description: old('description') )}}</textarea>
    </div>


    <div class="col-lg-6 mb-4">

        <div class="task_details mb-4">
            @if(isset($taskDetail))
            <div class="card">
                <div class="card-header">
                    <h6 class="">@lang('index.uploaded_files_and_images')</h6>
                </div>
                <div class="card-body">
                    @if(count($files) < 1 && count($images) < 1)
                        <div class="row">
                            <p class="text-muted">@lang('index.no_project_file_uploaded')</p>
                        </div>
                    @endif
                    <div class="row mb-4">
                        @forelse($images as $key => $imageData)
                            <div class="col-lg-3 col-md-6">
                                <div class="uploaded-image">
                                    <img class="w-100" style=""
                                        src="{{ asset(\App\Models\Attachment::UPLOAD_PATH.$imageData->attachment) }}"
                                        alt="document images">
                                    <a class="documentDelete" data-href="{{route('admin.attachment.delete',$imageData->id)}}">
                                        <i class="link-icon remove-image" data-feather="x"></i>
                                    </a>
                                </div>
                            </div>
                        @empty

                        @endforelse
                    </div>
                        <div class="row mb-4">
                            @forelse($files as $key => $fileData)
                                <div class="uploaded-files">
                                    <div class="row align-items-center">
                                        <div class="col-lg-1">
                                            <div class="file-icon">
                                                <i class="link-icon" data-feather="file-text"></i>
                                            </div>
                                        </div>
                                        <div class="col-lg-10">
                                            <a target="_blank" href="{{asset(\App\Models\Attachment::UPLOAD_PATH.$fileData->attachment)}}">
                                                {{asset(\App\Models\Attachment::UPLOAD_PATH.$fileData->attachment)}}
                                            </a>
                                        </div>

                                        <div class="col-lg-1">
                                            <a class="documentDelete" data-href="{{route('admin.attachment.delete',$fileData->id)}}">
                                                <i class="link-icon remove-files" data-feather="x"></i>
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            @empty

                            @endforelse
                        </div>
                </div>
            </div>
            @endif
        </div>

        <h6 class="mb-2">@lang('index.task_attachments')</h6>
        <div>
            <input id="image-uploadify" type="file"  name="attachments[]"
                   accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip"  multiple />
        </div>
    </div>
    <input type="hidden" readonly id="taskNotification" name="notification" value="0">

    <div class="col-12 text-center text-md-start">
        <button type="submit" class="btn btn-primary mb-2"><i class="link-icon" data-feather="{{isset($taskDetail)? 'edit-2':'plus'}}"></i> {{isset($taskDetail)? __('index.update_task') : __('index.create_task')}}</button>

        <button type="submit" id="withTaskNotification" class="btn btn-primary mb-2">
            <i class="link-icon" data-feather="{{isset($taskDetail)? 'edit-2':'plus'}}"></i>
            {{isset($taskDetail)?  __('index.update_send'): __('index.create_send')}}
        </button>
    </div>
</div>

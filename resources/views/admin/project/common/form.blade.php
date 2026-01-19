<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option value="{{ $value->id }}" {{ (isset($projectDetail) && $projectDetail->branch_id == $value->id) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="title" class="form-label">{{ __('index.project_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" required value="{{ ( isset( $projectDetail) ?  $projectDetail->name: old('name') )}}"
               autocomplete="off" placeholder="{{ __('index.project_name') }}">
    </div>

    @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="start_date" class="form-label">{{ __('index.project_start_date') }} <span style="color: red">*</span> </label>
            <input type="text" id="nepali_startDate" name="start_date" value="{{ ( isset( $projectDetail) ?  $projectDetail->start_date: old('start_date') )}}"
                   placeholder="yyyy-mm-dd" class="form-control startDate"/>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <label for="deadline" class="form-label">{{ __('index.project_deadline') }} <span style="color: red">*</span> </label>
            <input type="text" id="nepali_deadline" name="deadline" value="{{ ( isset( $projectDetail) ?  $projectDetail->deadline: old('deadline') )}}"
                   placeholder="yyyy-mm-dd" class="form-control deadline"/>
        </div>
    @else
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="start_date" class="form-label">{{ __('index.project_start_date') }} <span style="color: red">*</span> </label>
            <input type="date" class="form-control" id="start_date" name="start_date" required value="{{ ( isset( $projectDetail) ?  $projectDetail->start_date: old('start_date') )}}"
                   autocomplete="off" >
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <label for="deadline" class="form-label">{{ __('index.project_deadline') }} <span style="color: red">*</span> </label>
            <input type="date" class="form-control" id="deadline" name="deadline" required value="{{ ( isset( $projectDetail) ?  $projectDetail->deadline: old('deadline') )}}"
                   autocomplete="off" >
        </div>
    @endif

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="status" class="form-label">{{ __('index.project_status') }}</label>
        <select class="form-select" id="status" name="status">
            <option value="" {{isset($projectDetail) ? '' : 'selected'}} disabled>{{ __('index.select_project_status') }}</option>
            @foreach(\App\Models\Project::STATUS as $value)
                <option value="{{$value}}" {{ (isset($projectDetail) && ($projectDetail->status ) == $value) || (old('status') == $value) ? 'selected': '' }}>
                    {{\App\Helpers\PMHelper::STATUS[$value]}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="cost" class="form-label">{{ __('index.project_cost') }} <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="cost" name="cost" required value="{{ ( isset( $projectDetail) ?  $projectDetail->cost: old('cost') )}}"
               autocomplete="off" >
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="priority" class="form-label">{{ __('index.priority') }}</label>
        <select class="form-select" id="priority" name="priority">
            <option value="" {{isset($projectDetail) ? '' : 'selected'}} disabled>{{ __('index.select_priority') }}</option>
            @foreach(\App\Models\Project::PRIORITY as $value)
                <option value="{{$value}}" {{ (isset($projectDetail) && ($projectDetail->priority ) == $value) || ( old('priority') == $value) ? 'selected': '' }}>
                    {{ucfirst($value)}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="estimated_hours" class="form-label">{{ __('index.estimated_hours') }}</label>
        <input type="number" step="0.5" class="form-control" id="estimated_hours" name="estimated_hours" value="{{ ( isset( $projectDetail) ?  $projectDetail->estimated_hours: old('estimated_hours') )}}"
               autocomplete="off" >
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <div class="team_leader mb-4">
            <label for="department_ids" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
            <select class="col-md-12 from-select" id="department_ids" name="department_ids[]" multiple="multiple" required>
            </select>
        </div>

    </div>

    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-6">
                <div class="client_name mb-4">
                    <label for="client_id" class="form-label">{{ __('index.client') }} <span style="color: red">*</span></label>
                    <div class="d-flex align-items-center">
                        <select class="form-select" id="client_id" name="client_id">
                            <option value="" {{isset($projectDetail) ? '' : 'selected'}} disabled>{{ __('index.select_client') }}</option>
                            @foreach($clientLists as $key => $value)
                                <option value="{{$value->id}}" {{ (isset($projectDetail) && ($projectDetail->client_id ) == $value->id) || ( old('client_id') == $value->id) ? 'selected': '' }}>
                                    {{$value->name}}
                                </option>
                            @endforeach
                        </select>

                        @if(!isset($projectDetail))
                            <a class="btn btn-xs btn-primary add_client ms-2" data-bs-toggle="modal" data-bs-target="#addslider">
                                {{ __('index.add_client') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="upload_files mb-4">
                    <label for="upload" class="form-label">{{ __('index.upload_project_logo') }} <span style="color: red">*</span></label>
                    <input class="form-control" type="file" id="upload" accept=",.jpg,.jpeg,.png" name="cover_pic">
                </div>
                <div class="upload_files mb-4">
                    @if(isset($projectDetail) && $projectDetail->cover_pic)
                        <img src="{{asset(\App\Models\Project::UPLOAD_PATH.$projectDetail->cover_pic)}}" alt="" style="object-fit: contain" class="mt-3 w-25 rounded">
                    @endif
                </div>

            </div>

            <div class="col-lg-6">
                <div class="team_leader mb-4">
                    <label for="teamLeader" class="form-label">{{ __('index.project_leader') }} <span style="color: red">*</span></label>
                    <br>
                    <select class="col-md-12 from-select" id="projectLead" name="project_leader[]" multiple="multiple" required>
{{--                        @foreach($employees as $key => $value)--}}
{{--                            <option value="{{$value->id}}" {{ isset($projectDetail) && in_array($value->id,$leaderId)  ? 'selected' : '' }}>{{ ucfirst($value->name) }}</option>--}}
{{--                        @endforeach--}}
                    </select>
                </div>
                <div class="assign_member mb-4">
                    <label for="employee" class="form-label">{{ __('index.assign_member') }} <span style="color: red">*</span></label>
                    <br>
                    <select class="col-md-12 from-select" id="member" name="assigned_member[]" multiple="multiple" required>
{{--                        @foreach($employees as $key => $value)--}}
{{--                            <option value="{{$value->id}}" {{ isset($projectDetail) && in_array($value->id,$memberId)  ? 'selected' : '' }}>{{ ucfirst($value->name) }}</option>--}}
{{--                        @endforeach--}}
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="uploadify_plugin mb-3">
            <input id="image-uploadify" type="file" name="attachments[]" accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip" multiple>
        </div>

        @if(isset($projectDetail))
        <div class="uploadify_image_files">
            <label for="" class="form-label">{{ __('index.uploaded_files_images') }}</label>
            @if(count($files) < 1 && count($images) < 1)
            <p class="text-muted">{{ __('index.no_project_file_uploaded') }}</p>
            @endif
            <div class="uploaded_images">
                <div class="row">
                    @forelse($images as $key => $imageData)
                        <div class="col-lg-3 col-md-3 col-sm-6 mb-2">
                            <div class="uploaded-image">
                                <img class="w-100" src="{{ asset(\App\Models\Attachment::UPLOAD_PATH.$imageData->attachment) }}" alt="document images">
                                <a class="documentDelete" data-href="{{route('admin.attachment.delete',$imageData->id)}}">
                                    <i class="link-icon remove-image" data-feather="x"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
            <div class="uploaded_files mt-2">
                @forelse($files as $key => $fileData)
                <div class="row align-items-center">
                    <div class="col-lg-1 col-md-1 col-sm-1">
                        <div class="file-icon">
                            <i class="link-icon" data-feather="file-text"></i>
                        </div>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10">
                        <a target="_blank" href="{{asset(\App\Models\Attachment::UPLOAD_PATH.$fileData->attachment)}}">
                            {{asset(\App\Models\Attachment::UPLOAD_PATH.$fileData->attachment)}}
                        </a>
                    </div>

                    <div class="col-lg-1 col-md-1 col-sm-1">
                        <a class="documentDelete" data-href="{{route('admin.attachment.delete',$fileData->id)}}">
                            <i class="link-icon remove-files" data-feather="x"></i>
                        </a>
                    </div>
                </div>
                @empty
                @endforelse
            </div>
        </div>
    @endif
    </div>

    <div class="col-lg-6 mb-4">
        <label for="description" class="form-label">{{ __('index.description') }}<span style="color: red">*</span></label>
        <textarea class="form-control" name="description" id="tinymceExample" rows="4">{{ ( isset($projectDetail) ? $projectDetail->description: old('description') )}}</textarea>
    </div>

    <input type="hidden" readonly id="projectNotification" name="notification" value="0">

    <div class="col-lg-12 text-center text-md-start">
        <button type="submit" class="btn btn-primary mb-2">
            <i class="link-icon" data-feather="{{isset($projectDetail) ? 'edit-2' : 'plus'}}"></i>
            {{isset($projectDetail) ? __('index.update') : __('index.create')}}
        </button>

        <button type="submit" id="withProjectNotification" class="btn btn-primary mb-2">
            <i class="link-icon" data-feather="{{isset($projectDetail)? 'edit-2':'plus'}}"></i>
            {{isset($projectDetail)?  __('index.update_send'): __('index.create_send')}}
        </button>
    </div>
</div>

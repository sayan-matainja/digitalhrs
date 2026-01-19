<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">

            <option {{isset($tadaDetail) ? '' : 'selected'}}  disabled>{{ __('index.select_branch') }}</option>

            @if(isset($companyDetail))
                @foreach($companyDetail->branches()->get() as $key => $branch)
                    <option value="{{$branch->id}}"
                        {{ (isset($tadaDetail) && ($tadaDetail->branch_id ) == $branch->id)  ? 'selected': '' }}>
                        {{ucfirst($branch->name)}}</option>
                @endforeach
            @endif
        </select>
    </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="department_id" name="department_id">
            @if(isset($tadaDetail))
                @foreach($filteredDepartment as $department)
                    <option
                        value="{{ $department->id }}" {{ $department->id ==  $tadaDetail->department_id ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_department') }}</option>
            @endif


        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id"  >
            @if(isset($tadaDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ $user->id ==  $tadaDetail->employee_id ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @else
                <option selected  disabled>{{ __('index.select_employee') }}</option>
            @endif
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="title" class="form-label"> {{ __('index.title') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title" name="title" required value="{{ ( isset($tadaDetail) ?  $tadaDetail->title: old('title') )}}"
               autocomplete="off" placeholder="Enter TADA Title">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="expense" class="form-label"> {{ __('index.total_expense') }} <span style="color: red">*</span> </label>
        <input type="number" min="0" class="form-control" id="total_expense" name="total_expense" required value="{{ ( isset( $tadaDetail) ?  $tadaDetail->total_expense: old('total_expense') )}}"
               autocomplete="off" >
    </div>

    <div class="col-lg-6 mb-4">
        <label for="description" class="form-label">{{ __('index.description') }}</label>
        <textarea class="form-control" name="description" id="tinymceExample" rows="4">{{ ( isset($tadaDetail) ? $tadaDetail->description: old('description') )}}</textarea>
    </div>


        <div class="col-lg-6" >
            <div class="mb-4">
                <p class="mb-2">{{ __('index.uploaded_attachment') }} <span style="color: red">*</span></p>
                <div class="card p-4 pb-0">
                    <div class="row mb-4">
                        @forelse($attachments as $key => $data)
                            @if(!in_array(pathinfo(asset(\App\Models\TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment), PATHINFO_EXTENSION),['docx','pdf','doc','xls','txt'])  )
                                <div class="col-lg-3 mb-4">
                                    <div class="uploaded-image">
                                        <img class="w-100" style=""
                                            src="{{ asset(\App\Models\TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment) }}"
                                            alt="document images">
                                        <a class="delete" data-title="attachment image" data-href="{{route('admin.tadas.attachment-delete',$data->id)}}">
                                            <i class="link-icon remove-image" data-feather="x"></i>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="uploaded-files">
                                    <div class="row align-items-center">
                                        <div class="col-lg-1">
                                            <div class="file-icon">
                                                <i class="link-icon" data-feather="file-text"></i>
                                            </div>
                                        </div>
                                        <div class="col-lg-10">
                                            <a target="_blank" href="{{asset(\App\Models\TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment)}}">
                                                {{asset(\App\Models\TadaAttachment::ATTACHMENT_UPLOAD_PATH.$data->attachment)}}
                                            </a>
                                        </div>

                                        <div class="col-lg-1">
                                            <a class="delete" data-title="attachment file" data-href="{{route('admin.tadas.attachment-delete',$data->id)}}">
                                                <i class="link-icon remove-files" data-feather="x"></i>
                                            </a>
                                        </div>

                                    </div>

                                </div>
                            @endif

                        @empty
                            <div class="row">
                                <p class="text-muted">{{ __('index.no_attachment_file') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <p class="mb-2">{{ __('index.uploaded_attachment') }} <span style="color: red">*</span></p>
                <div>
                    <input id="image-uploadify" type="file"  name="attachments[]"
                        accept=".pdf,.jpg,.jpeg,.png,.docx,.doc,.xls,.txt,.zip"  multiple />
                </div>
            </div>
        </div>




    <div class="col-12">
        <button type="submit" class="btn btn-primary ">
            <i class="link-icon" data-feather="{{isset($tadaDetail)? 'edit-2':'plus'}}"></i>
            {{isset($tadaDetail)? __('index.update'):__('index.create') }} {{ Str::upper(__('index.tada')) }}
        </button>
    </div>
</div>








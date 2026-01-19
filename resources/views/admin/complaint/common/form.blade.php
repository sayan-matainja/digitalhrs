
<div class="row">
    <h5 class="mb-4"> Complaint from section</h5>
    <hr/>
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_from" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_from" name="branch_from">
                <option selected disabled>{{ __('index.select_branch') }}</option>
                @foreach($branch as $value)
                    <option value="{{ $value->id }}" {{ (isset($complaintDetail) && $complaintDetail->branch_id == $value->id) ? 'selected' : '' }}>
                        {{ ucfirst($value->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="{{ !isset(auth()->user()->branch_id) ? 'col-lg-4' : 'col-lg-3' }} }} col-md-6 mb-4">
        <label for="department_from" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
        <select class="form-select" id="department_from" name="department_from">
            @if(isset($complaintDetail))
                @foreach($filteredDepartment as $department)
                    <option value="{{ $department->id }}" {{ in_array($department->id, $departmentIds) ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="{{ !isset(auth()->user()->branch_id) ? 'col-lg-4' : 'col-lg-3' }} col-md-6 mb-4">
        <label for="complaint_from" class="form-label">{{ __('index.complaint_from') }} <span style="color: red">*</span></label>
        <select class="form-select" id="complaint_from" name="complaint_from">
            <option selected disabled>{{ __('index.select_employee') }}</option>

        </select>
    </div>
</div>
<div class="row">
    <h5 class="mb-4 "> Complaint to section</h5>
    <hr/>
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option value="{{ $value->id }}" {{ (isset($complaintDetail) && $complaintDetail->branch_id == $value->id) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="{{ !isset(auth()->user()->branch_id) ? 'col-lg-4' : 'col-lg-3' }} }} col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
        <select class="form-select" id="department_id" multiple name="department_id[]">
            @if(isset($complaintDetail))
                @foreach($filteredDepartment as $department)
                    <option value="{{ $department->id }}" {{ in_array($department->id, $departmentIds) ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="{{ !isset(auth()->user()->branch_id) ? 'col-lg-4' : 'col-lg-3' }} }} col-md-6 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.complain_to') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id[]" multiple>
            @if(isset($complaintDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ in_array($user->id, $employeeIds) ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
</div>
<div class="row">
 
    <div class="col-lg-6 mb-4">
        <label for="subject" class="form-label"> {{ __('index.subject') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="subject" name="subject" value="{{ ( isset( $complaintDetail) ?  $complaintDetail->subject: old('subject') )}}"
               autocomplete="off" placeholder="{{ __('index.subject') }}">
    </div>


    <div class="col-lg-6 mb-4">
        <label for="tinymceExample" class="form-label">{{ __('index.image') }}</label>
        <input class="form-control"
               type="file"
               id="image"
               name="image"
               value="{{ isset($complaintDetail) ? $complaintDetail->image : old('image') }}">
        @if(isset($complaintDetail->image))
            @php
                $fileExtension = pathinfo($complaintDetail->image, PATHINFO_EXTENSION);
            @endphp
            @if(in_array($fileExtension, ['jpeg', 'jpg', 'png', 'webp']))
                <img class="wd-200 ht-100" style="object-fit: cover;"
                     src="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                     alt="Certificate" data-bs-toggle="modal" data-bs-target="#certificateModal-{{ $complaintDetail->id }}">

                <div class="modal fade" id="certificateModal-{{ $complaintDetail->id }}" tabindex="-1" aria-labelledby="imageModalLabel-{{ $complaintDetail->index }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="imageModalLabel-{{ $complaintDetail->id }}">View Image <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button></h5>

                            </div>
                            <div class="modal-body text-center">
                                <img class="img-fluid" src="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}" alt="IBAN">
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($fileExtension === 'pdf')
                <embed src="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                       type="application/pdf" width="150" height="100" />
                <a href="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                   target="_blank" class="mt-2">Preview PDF</a>
            @else
                <a href="{{ asset(\App\Models\Complaint::UPLOAD_PATH . $complaintDetail->image) }}"
                   download class="mt-2">Download Document</a>
            @endif
        @endif
    </div>
    <div class="col-lg-6 mb-4">
        <label for="tinymceExample" class="form-label">{{ __('index.message') }}</label>
        <textarea class="form-control" name="message" id="tinymceExample" rows="1">{{ ( isset($complaintDetail) ? $complaintDetail->message: old('message') )}}</textarea>
    </div>

    <input type="hidden" readonly id="notification" name="notification" value="0">

@canany(['edit_complaint','create_complaint'])
        <div class="text-md-start text-center border-top pt-4">
            <button type="submit" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($complaintDetail)?  __('index.update'): __('index.create')}}
            </button>

            <button type="submit" id="withNotification" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($complaintDetail)?  __('index.update_send'): __('index.create_send')}}
            </button>
        </div>
    @endcanany
</div>





<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option value="{{ $value->id }}" {{ ((isset($warningDetail) && $warningDetail->branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
        <select class="form-select" id="department_id" multiple name="department_id[]">
            @if(isset($warningDetail))
                @foreach($filteredDepartment as $department)
                    <option value="{{ $department->id }}" {{ in_array($department->id, $departmentIds) ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="col-lg-4 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id[]" multiple>
            @if(isset($warningDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ in_array($user->id, $employeeIds) ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-4 d-md-flex d-lg-block d-block justify-content-between gap-4">
                <div class="subject-field mb-4 w-100">
                    <label for="subject" class="form-label"> {{ __('index.subject') }} <span style="color: red">*</span></label>
                    <input type="text" class="form-control" id="subject" name="subject" value="{{ ( isset( $warningDetail) ?  $warningDetail->subject: old('subject') )}}"
                     autocomplete="off" placeholder="{{ __('index.subject') }}">
                </div>
                <div class="warning-date mb-4 w-100">
                    <label for="warning_date" class="form-label">@lang('index.warning_date') <span style="color: red">*</span> </label>
                    @if($isBsEnabled)
                        <input type="text" class="form-control nepali_date" id="warning_date" name="warning_date" required value="{{ ( isset( $warningDetail) ?  \App\Helpers\AppHelper::taskDate($warningDetail->warning_date): old('warning_date') )}}"
                            autocomplete="off" >
                    @else
                        <input type="date" class="form-control" name="warning_date" required value="{{ ( isset( $warningDetail) ?  $warningDetail->warning_date: old('warning_date') )}}"
                            autocomplete="off" >
                    @endif
                </div>
            </div>
            <div class="col-lg-8 mb-4">
                <label for="tinymceExample" class="form-label">{{ __('index.message') }}</label>
                <textarea class="form-control" name="message" id="tinymceExample" rows="1">{{ ( isset($warningDetail) ? $warningDetail->message: old('message') )}}</textarea>
            </div>
        </div>
    </div>


    <input type="hidden" readonly id="notification" name="notification" value="0">

@canany(['edit_warning','create_warning'])
        <div class="text-center text-md-start border-top pt-4">
            <button type="submit" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($warningDetail)?  __('index.update'): __('index.create')}}
            </button>

            <button type="submit" id="withNotification" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($warningDetail)?  __('index.update_send'): __('index.create_send')}}
            </button>
        </div>
    @endcanany
</div>




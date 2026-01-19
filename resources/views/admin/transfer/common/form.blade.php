@php use App\Helpers\AppHelper; @endphp
<div class="row">

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="old_branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="old_branch_id" name="old_branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option
                    value="{{ $value->id }}" {{ ((isset($transferDetail) && $transferDetail->old_branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="old_department_id" class="form-label">{{ __('index.department') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="old_department_id" name="old_department_id">
            @if(isset($transferDetail))
                @foreach($filteredOldDepartment as $department)
                    <option
                        value="{{ $department->id }}" {{ $department->id ==  $transferDetail->old_department_id ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_department') }}</option>
            @endif
        </select>
    </div>

    <div class="col-lg-4 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id">

            @if(isset($transferDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ $user->id ==  $transferDetail->employee_id ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_employee') }}</option>
            @endif
        </select>
    </div>

    <div class="col-lg-4 mb-4">
        <label for="old_post_id" class="form-label">{{ __('index.post') }} <span style="color: red">*</span></label>
        <select class="form-select" id="old_post_id" name="old_post_id">
            @if(isset($transferDetail))
                <option selected value="{{ $transferDetail->old_post_id }}">{{ ucfirst($transferDetail->oldPost->post_name) }}</option>
            @endif
        </select>
    </div>
    <div class="col-lg-4 mb-4">
        <label for="old_supervisor_id" class="form-label">{{ __('index.supervisor') }} <span style="color: red">*</span></label>
        <select class="form-select" id="old_supervisor_id" name="old_supervisor_id">
            @if(isset($transferDetail))
                <option selected value="{{ $transferDetail->old_supervisor_id }}">{{ ucfirst($transferDetail->oldSupervisor->name) }}</option>
            @endif
        </select>
    </div>

    <div class="col-lg-4 mb-4">
        <label for="old_office_time_id" class="form-label">{{ __('index.office_time') }} <span style="color: red">*</span></label>
        <select class="form-select" id="old_office_time_id" name="old_office_time_id">
            @if(isset($transferDetail))
                <option selected value="{{ $transferDetail->old_office_time_id }}">{{ $transferDetail->oldOfficeTime->opening_time .' - '.  $transferDetail->oldOfficeTime->closing_time}}</option>
            @endif
        </select>
    </div>

</div>
<div class="row">
    <h5 class="col-lg-12 mt-4 mb-4"> {{ __('index.transfer_section') }}</h5>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="new_branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="new_branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option
                    value="{{ $value->id }}" {{ isset($transferDetail) && $transferDetail->branch_id == $value->id ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="new_department_id" class="form-label">{{ __('index.department') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="new_department_id" name="department_id">
            @if(isset($transferDetail))
                @foreach($filteredDepartment as $department)
                    <option
                        value="{{ $department->id }}" {{ $department->id ==  $transferDetail->department_id ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_department') }}</option>
            @endif
        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="new_post_id" class="form-label">{{ __('index.post') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="new_post_id" name="post_id">
            @if(isset($transferDetail))
                @foreach($filteredPosts as $post)
                    <option
                        value="{{ $post->id }}" {{ $post->id ==  $transferDetail->post_id ? 'selected' : '' }}>
                        {{ ucfirst($post->post_name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_post') }}</option>
            @endif
        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="new_supervisor_id" class="form-label">{{ __('index.supervisor') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="new_supervisor_id" name="supervisor_id">
            @if(isset($transferDetail))
                @foreach($filteredSupervisor as $supervisor)
                    <option
                        value="{{ $supervisor->id }}" {{ $supervisor->id ==  $transferDetail->supervisor_id ? 'selected' : '' }}>
                        {{ ucfirst($supervisor->name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_supervisor') }}</option>
            @endif
        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="new_office_time_id" class="form-label">{{ __('index.office_time') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="new_office_time_id" name="office_time_id">
            @if(isset($transferDetail))
                @foreach($filteredOfficeTime as $office_time)
                    <option
                        value="{{ $office_time->id }}" {{ $office_time->id ==  $transferDetail->office_time_id ? 'selected' : '' }}>
                        {{$office_time->opening_time .' - '. $office_time->closing_time }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_office_time') }}</option>
            @endif
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="transfer_date" class="form-label">@lang('index.transfer_date') <span
                style="color: red">*</span> </label>
        @if($isBsEnabled)
            <input type="text" class="form-control nepali_date" id="transfer_date" name="transfer_date" required
                   value="{{ ( isset( $transferDetail) ?  AppHelper::taskDate($transferDetail->transfer_date): old('transfer_date') )}}"
                   autocomplete="off">
        @else
            <input type="date" class="form-control" name="transfer_date" required
                   value="{{ ( isset( $transferDetail) ?  $transferDetail->transfer_date: old('transfer_date') )}}"
                   autocomplete="off">
        @endif
    </div>

    <div class="col-lg-6 mb-4">
        <label for="tinymceExample" class="form-label">{{ __('index.description') }}</label>
        <textarea class="form-control" name="description" id="tinymceExample"
                rows="1">{{ ( isset($transferDetail) ? $transferDetail->description: old('description') )}}</textarea>
    </div>


{{--    <div class="col-lg-4">--}}

{{--        <div class="mb-4 w-100">--}}
{{--            <label for="status" class="form-label">@lang('index.status')</label>--}}
{{--            <select class="form-select" id="status" name="status">--}}
{{--                @foreach($status as $stat)--}}
{{--                    <option--}}
{{--                        value="{{ $stat->value }}" {{  isset($transferDetail) && $stat->value ==  $transferDetail->status ? 'selected' : '' }}>--}}
{{--                        {{ ucfirst($stat->value) }}--}}
{{--                    </option>--}}
{{--                @endforeach--}}
{{--            </select>--}}
{{--        </div>--}}
{{--    </div>--}}



    <input type="hidden" readonly id="notification" name="notification" value="0">

    @canany(['edit_transfer','create_transfer'])
        <div class="text-center text-md-start border-top pt-4">
            <button type="submit" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($transferDetail)?  __('index.update'): __('index.create')}}
            </button>

            <button type="submit" id="withNotification" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($transferDetail)?  __('index.update_send'): __('index.create_send')}}
            </button>
        </div>
    @endcanany
</div>




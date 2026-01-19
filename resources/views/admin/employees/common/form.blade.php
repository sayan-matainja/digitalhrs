@php use App\Models\User; @endphp
@php use App\Models\EmployeeAccount; @endphp
<div class="mb-2 text-md-start text-center"><small>{!! __('index.all_fields_required') !!}</small></div>
<style>
    .is-invalid {
        border-color: red !important;
    }

    .is-invalid + .error-message {
        display: block;
        color: red !important;
    }

    .error-message {
        display: none;
        color: red !important;
    }
</style>
<div class="card mb-4">
    <div class="card-body pb-2">
        <div class="profile-detail">
            <h5 class="mb-3 border-bottom pb-3">{{ __('index.personal_detail') }}</h5>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="employee_code" class="form-label">{{ __('index.employee_code') }} </label>
                    <input type="text" class="form-control"
                           id="employee_code"
                           name="employee_code"
                           readonly
                           style="pointer-events: none;"
                           value="{{ ( isset($userDetail) ? $userDetail->employee_code: $employeeCode )}}" autocomplete="off"
                           placeholder="{{ __('index.employee_code') }}" required>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="name" class="form-label"> {{ __('index.name') }} <span style="color: red">*</span></label>
                    <input type="text" class="form-control"
                           id="name"
                           name="name"
                           value="{{ ( isset($userDetail) ? $userDetail->name: old('name') )}}" autocomplete="off"
                           placeholder="{{ __('index.enter_name') }}" required>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="address" class="form-label"> {{ __('index.address') }}</label>
                    <input type="text"
                           class="form-control"
                           id="address"
                           name="address"
                           value="{{ (isset($userDetail) ? ($userDetail->address): old('address'))}}"
                           autocomplete="off" placeholder="{{ __('index.enter_employee_address') }}">
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="email" class="form-label">{{ __('index.email') }} <span style="color: red">*</span></label>
                    <input type="email" class="form-control" id="email" name="email"
                           value="{{ ( isset($userDetail) ? $userDetail->email: old('email') )}}" required
                           autocomplete="off" placeholder="{{ __('index.enter_email') }}">
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="number" class="form-label">{{ __('index.phone_no') }}</label>
                    <input type="number" class="form-control" id="phone" name="phone"
                           value="{{ isset($userDetail)? $userDetail->phone: old('phone') }}"
                           autocomplete="off" placeholder="{{ __('index.phone_no') }}">
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="dob" class="form-label"> {{ __('index.dob') }} </label>
                    @if($bsEnabled)
                        <input type="text" class="form-control birthDate" id="dob" name="dob"
                               value="{{(isset($userDetail->dob) ? \App\Helpers\AppHelper::taskDate($userDetail->dob): old('dob') )}}"
                               autocomplete="off"
                               placeholder="{{ __('index.dob') }}">
                    @else
                        <input type="date" class="form-control" id="dob" name="dob"
                               value="{{ ( isset($userDetail) ? ($userDetail->dob): old('dob') )}}"
                               autocomplete="off" placeholder="{{ __('index.dob') }}">
                    @endif

                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="gender" class="form-label">{{ __('index.gender') }}</label>
                    <select class="form-select" id="gender" name="gender">
                        <option value="" {{isset($userDetail) || old('gender') ? '' : 'selected'}}  disabled>{{ __('index.select_gender') }}
                        </option>
                        @foreach(User::GENDER as $value)
                            <option
                                value="{{$value}}" {{ isset($userDetail) && ($userDetail->gender ) == $value || old('gender') == $value ? 'selected': '' }}>
                                {{ucfirst($value)}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="marital_status" class="form-label">{{ __('index.marital_status') }}</label>
                    <select class="form-select" id="marital_status" name="marital_status" required>
                        <option value="" {{isset($userDetail) || old('marital_status') ? '' : 'selected'}}  disabled>
                            {{ __('index.choose_marital_status') }}
                        </option>
                        @foreach(User::MARITAL_STATUS as $value)
                            <option value="{{ $value }}"
                                {{ isset($userDetail) && ($userDetail->marital_status ) == $value || old('marital_status') == $value ? 'selected': '' }}>
                                {{ucfirst($value)}}
                            </option>
                        @endforeach
                    </select>
                </div>


                <div class="col-lg-4 mb-3">
                    <label for="avatar" class="form-label">{{ __('index.upload_avatar') }} <span style="color: red">*</span> </label>
                    <input class="form-control"
                           type="file"
                           id="avatar"
                           name="avatar"
                           accept="image/*"
                           value="{{ isset($userDetail) ? $userDetail->avatar: old('avatar') }}" {{isset($userDetail) ? '': 'required'}} >

                    <img class="mt-2 rounded {{(isset($userDetail) && $userDetail->avatar) ? '': 'd-none'}}"
                         id="image-preview"
                         src="{{ (isset($userDetail) && $userDetail->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$userDetail->avatar) : ''}}"
                         style="object-fit: contain"
                         width="100"
                         height="100"
                    >
                </div>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-6 mb-3 empl-desc">
                            <label for="remarks" class="form-label">{{ __('index.description') }}</label>
                            <textarea class="form-control" name="remarks" id="tinymceExample"
                                    rows="2">{{ ( isset($userDetail) ? $userDetail->remarks: old('remarks') )}}</textarea>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-12 col-md-4 mb-3">
                                    <label for="username" class="form-label">{{ __('index.username') }} <span style="color: red">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="{{ ( isset($userDetail) ? $userDetail->username: old('username') )}}"
                                        required
                                        autocomplete="off" placeholder="{{ __('index.enter_username') }}">
                                </div>
                                @if(!isset($userDetail))
                                    <div class="col-lg-12 col-md-4 mb-3">
                                        <label for="password" class="form-label">{{ __('index.password') }} <span style="color: red">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            value="{{old('password')}}" autocomplete="off" placeholder="{{ __('index.enter_password') }}" required>
                                    </div>
                                @endif

                                <div class="col-lg-12 col-md-4 mb-3">
                                    <label for="role" class="form-label">{{ __('index.role') }} <span style="color: red">*</span></label>
                                    <select class="form-select" id="role" name="role_id" required>
                                        <option value="" {{isset($userDetail) || old('role_id')  ? '': 'selected'}}  disabled>{{ __('index.select_role') }}
                                        </option>
                                        @if($roles)
                                            @foreach($roles as $key =>  $value)
                                                <option value="{{$value->id}}"
                                                    {{ isset($userDetail) && ($userDetail->role_id ) == $value->id  || old('role_id') == $value->id ? 'selected': '' }}> {{ucfirst($value->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body pb-2">
        <div class="company-detail">
            <h5 class="mb-3 border-bottom pb-3">{{ __('index.company_detail') }}</h5>
            <div class="row">
                @if(!isset(auth()->user()->branch_id))
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
                    <select class="form-select" id="branch" name="branch_id">
                        <option value="" {{!isset($userDetail) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
                        </option>
                        @if(isset($companyDetail))
                            @foreach($companyDetail->branches()->get() as $key => $branch)
                                <option value="{{$branch->id}}"
                                    {{ (isset($userDetail) && ($userDetail->branch_id ) == $branch->id)  ? 'selected': '' }}>
                                    {{ucfirst($branch->name)}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @endif

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="department" class="form-label">{{ __('index.departments') }}</label>
                    <select class="form-select" id="department" name="department_id">
                        <option selected disabled>{{ __('index.select_department') }}
                        </option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="post" class="form-label">{{ __('index.post') }}</label>
                    <select class="form-select" id="post" name="post_id">
                        @if(isset($userDetail))
                            @foreach($filteredPosts as $post)
                                <option
                                    value="{{ $post->id }}" {{ $post->id ==  $userDetail->post_id ? 'selected' : '' }}>
                                    {{ ucfirst($post->post_name) }}
                                </option>
                            @endforeach
                        @else
                            <option selected disabled>{{ __('index.select_post') }}</option>
                        @endif
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="supervisor" class="form-label">{{ __('index.supervisor') }}</label>
                    <select class="form-select" id="supervisor" name="supervisor_id">
                        @if(isset($userDetail))
                            @foreach($filteredSupervisor as $supervisor)
                                @if( $supervisor->id !== $userDetail->id)
                                    <option
                                        value="{{ $supervisor->id }}" {{ $supervisor->id ==  $userDetail->supervisor_id ? 'selected' : '' }}>
                                        {{ ucfirst($supervisor->name) }}
                                    </option>
                                @endif
                            @endforeach
                        @else
                            <option selected disabled>{{ __('index.select_supervisor') }}</option>
                        @endif
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="employment_type" class="form-label">{{ __('index.employment_type') }}
                    </label>
                    <select class="form-select" id="employment_type" name="employment_type">
                        <option value="" {{isset($userDetail) || old('employment_type') ? '': 'selected'}}  disabled>
                            {{ __('index.select_employment_type') }}
                        </option>
                        @foreach(User::EMPLOYMENT_TYPE as $value)
                            <option
                                value="{{$value}}" {{ isset($userDetail) && ($userDetail->employment_type ) == $value || old('employment_type') == $value ? 'selected': '' }}>
                                {{ucfirst($value)}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="officeTime" class="form-label">{{ __('index.office_time') }}</label>
                    <select class="form-select" id="officeTime" name="office_time_id">

                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="joining_date" class="form-label">{{ __('index.joining_date') }}</label>
                    @if($bsEnabled)
                        <input type="text" class="form-control joiningDate" id="joining_date" name="joining_date"
                               value="{{(isset($userDetail->joining_date) ? \App\Helpers\AppHelper::taskDate($userDetail->joining_date): old('joining_date') )}}"
                               autocomplete="off"
                               placeholder="{{ __('index.enter_joining_date') }}">
                    @else
                        <input type="date" class="form-control" id="joining_date" name="joining_date"
                               value="{{(isset($userDetail) ? ($userDetail->joining_date): old('joining_date') )}}"
                               autocomplete="off"
                               placeholder="{{ __('index.enter_joining_date') }}">
                    @endif

                </div>

                <div class="col-lg-4 col-md-6 mb-3">
                    <label for="workspace_type" class="form-label">{{ __('index.workspace') }}</label>
                    <select class="form-select" id="workspace_type" name="workspace_type">
                        <option value="" {{ isset($userDetail) || old('workspace_type') ? '': 'selected'}}  disabled>
                            {{ __('index.select_workspace') }}
                        </option>
                        <option value="{{User::FIELD}}"
                            {{ isset($userDetail) && ($userDetail->workspace_type ) == User::FIELD || old('workspace_type') == User::FIELD ? 'selected': '' }}>
                            {{ __('index.field') }}
                        </option>
                        <option value="{{User::OFFICE}}"
                            {{ isset($userDetail) && ($userDetail->workspace_type ) == User::OFFICE || old('workspace_type') == User::OFFICE ? 'selected': '' }}>
                            {{ __('index.office') }}
                        </option>

                    </select>
                </div>
                <div class="col-lg-4 col-md-6 mt-5">
                    <input type="checkbox" name="allow_holiday_check_in" id="allow_holiday_check_in" {{ isset($userDetail) && ($userDetail->allow_holiday_check_in == 1) ? 'checked': '' }}>
                    {{ __('index.allow_holiday_check_in') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-lg-6 d-flex">
        <div class="card mb-4 w-100">
            <div class="card-body">
                <div class="bank-detail">
                    <h5 class="mb-3 border-bottom pb-3">{{ __('index.leave_detail') }}</h5>
                    <label for="leave_allocated" class="form-label">{{ __('index.leave_allocated') }}</label>
                    <input type="number" class="form-control mb-2" min="0"
                           id="leave_allocated"
                           name="leave_allocated"
                           oninput="validity.valid||(value='');"
                           value="{{ isset($userDetail) ? $userDetail->leave_allocated : old('leave_allocated') }}"
                           autocomplete="off" placeholder="{{ __('index.leave_allocated') }}">

                    <div id="error-message" style="color: red !important; display: none;"></div>
                    <table class="table table-responsive">
                        <h5 class="my-3">{{ __('index.assigned_leaves') }}</h5>
                        <thead>
                        <tr>
                            <th>{{ __('index.leave') }}</th>
                            <th>{{ __('index.no_of_days') }}</th>
                            <th>{{ __('index.is_active') }}</th>
                        </tr>
                        </thead>
                        <tbody id="leave-types-table">
                        @if(isset($leaveTypes))
                            @for ($k = 0; $k < count($leaveTypes); $k++)
                                <tr>
                                    <td>
                                        {{ $leaveTypes[$k]->name }}
                                        <input type="hidden" name="leave_type_id[{{ $k }}]" value="{{ $leaveTypes[$k]->id }}">
                                    </td>
                                    @if (isset($employeeLeaveTypes[$k]))
                                        @php $leaveType = $employeeLeaveTypes[$k]; @endphp
                                    @endif
                                    <td>
                                        <input type="number" min="0" class="form-control leave-days"
                                               value="{{ $leaveType->days ?? '' }}"
                                               oninput="validity.valid||(value='');"
                                               placeholder="{{ __('index.total_leave_days') }}"
                                               name="days[{{ $k }}]">
                                        <span class="error-message" style="display: none; color: red;">{{ __('index.required_field') }}.</span>
                                    </td>
                                    <td>
                                        <input class="me-1 is-active-checkbox" type="checkbox"
                                               {{ isset($leaveType->is_active) && $leaveType->is_active == 1 ? 'checked' : '' }}
                                               name="is_active[{{ $k }}]" value="1">{{ __('index.is_active') }}
                                    </td>
                                </tr>
                            @endfor
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 d-flex">
        <div class="card mb-4 w-100">
            <div class="card-body pb-0">
                <div class="bank-detail">
                    <h5 class="mb-3 border-bottom pb-3">{{ __('index.bank_detail')}}</h5>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="bank_name" class="form-label">{{ __('index.bank_name')}} <span
                                    style="color: red">*</span></label>
                            <input type="text" class="form-control"
                                   id="bank_name"
                                   name="bank_name"
                                   value="{{ isset($userDetail?->accountDetail) ? $userDetail?->accountDetail?->bank_name: old('bank_name') }}"
                                   autocomplete="off" placeholder="{{ __('index.bank_name')}}" required>
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="bank_account_no" class="form-label">{{ __('index.bank_account_number')}} <span
                                    style="color: red">*</span></label>
                            <input type="number"
                                   class="form-control"
                                   id="bank_account_no"
                                   name="bank_account_no"
                                   value="{{ isset($userDetail?->accountDetail) ? $userDetail?->accountDetail?->bank_account_no: old('bank_account_no') }}"
                                   autocomplete="off"
                                   placeholder=" {{ __('index.bank_account_number')}}" required>
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="account_holder" class="form-label">{{ __('index.account_holder_name')}} <span style="color: red">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   id="account_holder"
                                   name="account_holder"
                                   value="{{isset($userDetail) ? $userDetail?->accountDetail?->account_holder: old('account_holder') }}"
                                   autocomplete="off"
                                   required
                                   placeholder="{{ __('index.account_holder_name') }}">
                        </div>

                        <div class="col-lg-6 col-md-6 mb-4">
                            <label for="bank_account_type" class="form-label">{{ __('index.bank_account_type')}}<span style="color: red">*</span></label>
                            <select class="form-select" id="bank_account_type" name="bank_account_type" required>
                                <option value="" {{isset($userDetail) || old('bank_account_type') ? '': 'selected'}} >
                                    {{ __('index.select_account_type') }}
                                </option>
                                @foreach(EmployeeAccount::BANK_ACCOUNT_TYPE as $value)
                                    <option
                                        value="{{ $value }}" {{ isset($userDetail?->accountDetail) && ($userDetail?->accountDetail?->bank_account_type ) == $value || old('bank_account_type') == $value ? 'selected': '' }}>
                                        {{ucfirst($value)}}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<button type="submit" class="btn btn-primary">
    <i class="link-icon" data-feather="plus"></i> {{isset($userDetail)? __('index.update_user'):__('index.create_user')}}
</button>

<div class="row">

    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id" required>
            <option  {{!isset($leaveApprovalDetail) || old('branch_id') ? 'selected': ''}}  disabled>{{ __('index.select_branch') }}
            </option>
            @if(isset($companyDetail))
                @foreach($companyDetail->branches()->get() as $key => $branch)
                    <option value="{{$branch->id}}"
                        {{ (isset($leaveApprovalDetail) && ($leaveApprovalDetail->branch_id ) == $branch->id) ? 'selected': '' }}>
                        {{ucfirst($branch->name)}}</option>
                @endforeach
            @endif
        </select>
    </div>
    @endif
    <!-- Subject Field -->
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="subject" class="form-label">{{ __('index.subject') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="subject" name="subject" value="{{ ( isset( $leaveApprovalDetail) ?  $leaveApprovalDetail->subject: old('subject') )}}" required>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="related" class="form-label">{{ __('index.leave_types') }} <span style="color: red">*</span></label>
        <select class="form-select" id="related" name="leave_type_id" required>
            <option value="" disabled selected>{{ __('index.select_leave_type') }}</option>

        </select>
    </div>

    <!-- Departments Field -->
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="departments" class="form-label">{{ __('index.department') }}</label>
        <select class="form-select" id="departments" multiple name="department_id[]">
            <option disabled>{{ __('index.select_department') }}</option>

        </select>
    </div>

    <div class="col-12">
        <div class="approval-set">
            <div class="d-flex justify-content-between align-items-center">
                <h5>{{ __('index.approval_process') }}</h5>
                <button type="button" class="btn btn-success btn-sm" id="add-approver">+</button>
            </div>
            <div class="approved-list mt-3 border-top pt-3">
                <ul id="sortable">
                    <li class="ui-state-default approver-row template" style="display: none !important;">
                        <div class="row">
                            <div class="col-lg-3 col-md-3 mb-4">
                                <label class="form-label">{{ __('index.approver') }}</label>
                                <select class="form-select approver-select" name="approver[]">
                                    <option disabled selected>{{ __('index.select_approver') }}</option>
                                    @foreach(\App\Enum\LeaveApproverEnum::cases() as $approver)
                                        <option value="{{ $approver->value }}">{{ __('index.' . $approver->value) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 mb-4 employee-wrapper" style="display:none;">
                                <label class="form-label">{{ __('index.role') }}</label>
                                <select class="form-select staff-select" name="role_id[]">
                                    <option selected disabled>{{ __('index.select_role') }}</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-3 mb-4 employee-wrapper" style="display:none;">
                                <label class="form-label">{{ __('index.employee') }}</label>
                                <select class="form-select user-dropdown" name="user_id[]">
                                    <option selected disabled>{{ __('index.select_employee') }}</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-2 mb-4 d-flex align-items-center mt-sm-4"></div>
                            <div class="col-lg-1 col-md-1 mb-4 d-flex align-items-center justify-content-md-end mt-sm-4">
                                <i class="link-icon" data-feather="move"></i>
                            </div>
                        </div>
                    </li>
                    @if(isset($leaveApprovalDetail))
                        @foreach($leaveApprovalDetail->approvalProcess as $index => $process)
                            <li class="ui-state-default approver-row" data-index="{{ $index }}">
                                <div class="row">
                                        <div class="col-lg-3 col-md-3 mb-4">
                                            <label for="approver" class="form-label">{{ __('index.approver') }}</label>
                                            <select class="form-select approver-select" name="approver[{{ $index }}]">
                                                <option disabled>{{ __('index.select_approver') }}</option>
                                                @foreach(\App\Enum\LeaveApproverEnum::cases() as $approver)
                                                    <option value="{{ $approver->value }}" {{ $approver->value == $process->approver ? 'selected' : '' }}>
                                                        {{ __('index.' . $approver->value) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3 col-md-3 mb-4 employee-wrapper" style="{{ $process->approver === 'specific_personnel' ? '' : 'display:none;' }}">
                                            <label for="staff" class="form-label">{{ __('index.role') }}</label>
                                            <select class="form-select staff-select" name="role_id[{{ $index }}]">
                                                <option selected disabled>{{ __('index.select_role') }}</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->id }}" {{ $role->id == $process->role_id ? 'selected' : '' }}>
                                                        {{ ucfirst($role->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3 col-md-3 mb-4 employee-wrapper" style="{{ $process->approver === 'specific_personnel' ? '' : 'display:none;' }}">
                                            <label for="staff" class="form-label">{{ __('index.employee') }}</label>
                                            <select class="form-select user-dropdown" name="user_id[{{$index}}]">
                                                <option selected disabled>{{ __('index.select_employee') }}</option>
                                                @foreach($process->users as $user)
                                                <option value="{{ $user->id }}" {{ $user->id == $process->user_id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-2 col-md-2 mb-4 d-flex align-items-center mt-sm-4">
                                            <button type="button" class="btn btn-danger btn-sm remove-approver">x</button>
                                        </div>
                                        <div class="col-lg-1 col-md-1 mb-4 d-flex align-items-center justify-content-md-end mt-sm-4">
                                            <i class="link-icon" data-feather="move"></i>
                                        </div>

                                    </div>
                            </li>
                        @endforeach
                    @else
                        <li class="ui-state-default approver-row">
                            <div class="row">
                                <div class="col-md-3 mb-4">
                                    <label for="approver" class="form-label">{{ __('index.approver') }}</label>
                                    <select class="form-select approver-select" name="approver[]">
                                        @foreach(\App\Enum\LeaveApproverEnum::cases() as $approver)
                                            <option value="{{ $approver->value }}">{{ __('index.' . $approver->value) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-4 employee-wrapper" style="display:none;">
                                    <label for="role_id" class="form-label">{{ __('index.role') }}</label>
                                    <select class="form-select staff-select" name="role_id[]">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-4 employee-wrapper" style="display:none;">
                                    <label for="user_id" class="form-label">{{ __('index.employee') }}</label>
                                    <select class="form-select user-dropdown" name="user_id[]">

                                    </select>
                                </div>

                                <div class="col-md-2 d-flex align-items-center">

                                </div>
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="col-12">
        <button type="submit" class="btn btn-primary">{{ __('index.save') }}</button>
    </div>
</div>

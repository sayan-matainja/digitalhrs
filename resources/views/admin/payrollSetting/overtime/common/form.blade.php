<div class="row">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-3">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-control" id="branch_id" name="branch_id" required>
                <option selected disabled>{{ __('index.select_branch') }}</option>
                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        <option value="{{$branch->id}}" {{ isset($overtime) && $overtime->branch_id == $branch->id ? 'selected' : '' }}>
                            {{ucfirst($branch->name)}}</option>
                    @endforeach
                @endif
            </select>
        </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="payroll_type">{{ __('index.payroll_type') }} <span style="color: red">*</span></label>
        <select class="form-control" name="payroll_type" id="payroll_type" required>
            <option selected disabled>{{ __('index.select_payroll_type') }}</option>
            <option {{ isset($overtime) && $overtime->payroll_type == 'annual' ? 'selected' : '' }} value="annual">{{ __('index.annual') }}</option>
            <option {{ isset($overtime) && $overtime->payroll_type == 'hourly' ? 'selected' : '' }} value="hourly">{{ __('index.hourly') }}</option>
        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="title" class="form-label"> {{ __('index.title') }} <span style="color: red">*</span></label>
        <input type="text"
               class="form-control"
               id="title" step="0.1" min="0" name="title" required
               value="{{ isset($overtime) ? $overtime->title : old('title') }}"
               autocomplete="off"
               placeholder="{{ __('index.title') }}">
        @error('title')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="max_daily_ot_hours" class="form-label"> {{ __('index.daily_ot') }} <span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="max_daily_ot_hours" step="0.1" min="0" name="max_daily_ot_hours" required
               value="{{ isset($overtime) ? $overtime->max_daily_ot_hours: old('max_daily_ot_hours') }}"
               autocomplete="off"
               placeholder="{{ __('index.placeholder_daily_ot') }}">
        @error('max_daily_ot_hours')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="max_weekly_ot_hours" class="form-label"> {{ __('index.weekly_ot') }} <span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="max_weekly_ot_hours" step="0.1" min="0" name="max_weekly_ot_hours" required
               value="{{ isset($overtime) ? $overtime->max_weekly_ot_hours: old('max_weekly_ot_hours') }}"
               autocomplete="off"
               placeholder="{{ __('index.placeholder_weekly_ot') }}">
        @error('max_weekly_ot_hours')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="max_monthly_ot_hours" class="form-label">{{ __('index.monthly_ot') }}<span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="max_monthly_ot_hours" step="0.1" min="0" name="max_monthly_ot_hours" required
               value="{{ isset($overtime) ? $overtime->max_monthly_ot_hours: old('max_monthly_ot_hours') }}"
               autocomplete="off"
               placeholder="{{ __('index.placeholder_monthly_ot') }}">
        @error('max_monthly_ot_hours')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="valid_after_hour" class="form-label">{{ __('index.ot_valid_after_hour') }} <span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="valid_after_hour" step="0.1" min="0" name="valid_after_hour" required
               value="{{ isset($overtime) ? $overtime->valid_after_hour: old('valid_after_hour') }}"
               autocomplete="off"
               placeholder="{{ __('index.placeholder_ot_valid_after_hour') }}">
        @error('valid_after_hour')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="" class="form-label">{{ __('index.rate_type') }}  <span style="color: red">*</span> </label>
        <select class="col-md-12 form-select" id="pay_type" name="pay_type" required>
            <option selected disabled>{{ __('index.select_rate_type') }}</option>
            <option {{ (old('pay_type')  || (isset($overtime) && $overtime->pay_type) == 0) ? 'selected' :'' }} value="0">{{ __('index.percent') }}</option>
            <option {{ (old('pay_type')  || (isset($overtime) && $overtime->pay_type) == 1) ? 'selected' :'' }} value="1">{{ __('index.amount') }}</option>
        </select>
    </div>
    <div class="col-lg-6 mb-3 pay_percent {{ (old('pay_type')  || (isset($overtime) && $overtime->pay_type) == 0) ? '' :'d-none' }}" >
        <label for="pay_percent" class="form-label">{{ __('index.overtime_rate_percent') }} <span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="pay_percent" step="0.1" min="0" name="pay_percent"
               value="{{ isset($overtime) ? $overtime->pay_percent: old('pay_percent') }}"
               autocomplete="off"
               placeholder="{{ __('index.placeholder_overtime_rate_percent') }}">
        @error('pay_percent')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-3 pay_rate {{ (old('pay_type')  || (isset($overtime) && $overtime->pay_type) == 1) ? '' :'d-none' }}">
        <label for="overtime_pay_rate" class="form-label">{{ __('index.overtime_pay_rate') }}  <span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="overtime_pay_rate" step="0.1" min="0" name="overtime_pay_rate"
               value="{{ isset($overtime) ? $overtime->overtime_pay_rate: old('overtime_pay_rate') }}"
               autocomplete="off"
               placeholder="{{ __('index.placeholder_overtime_pay_rate') }}">
        @error('overtime_pay_rate')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
        <select class="form-control" id="department_id" name="department_id[]" multiple required>

            @if(isset($overtime))
                @foreach($departments as $department)
                    <option value="{{$department->id}}" {{ in_array($department->id,$departmentIds) ? 'selected' : '' }}> {{ $department->dept_name }}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="employee_id" class="form-label">{{ __('index.assign_employee') }} <span style="color: red">*</span></label>
        <select class="col-md-12 form-select" id="employee_id" name="employee_id[]" multiple="multiple" required>
            @if(isset($overtime))
                @foreach($employees as $employee)
                    <option value="{{$employee->id}}" {{ in_array($employee->id,$employeeIds) ? 'selected' : '' }}> {{ $employee->name }}</option>
                @endforeach
            @endif
        </select>
    </div>

    @can('overtime_setting')
        <div class="col-12">
            <button type="submit" class="btn btn-primary ">
                <i class="link-icon" data-feather="{{ isset($overtime) ? 'edit-2':'plus'}}"></i>
                {{ isset($overtime) ? __('index.update') :__('index.save') }}
            </button>
        </div>
    @endcan
</div>

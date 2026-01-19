<div class="row">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option
                    {{ !isset($requestDetail) || old('branch_id') ? 'selected' : '' }} disabled>{{ __('index.select_branch') }}</option>
                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        <option value="{{ $branch->id }}"
                            {{ ((isset($requestDetail) && $requestDetail->branch_id == $branch->id) || old('branch_id') == $branch->id) ? 'selected' : '' }}>
                            {{ ucfirst($branch->name) }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('branch_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    @endif
    @if(!auth('admin')->check() && auth()->check())
        <input type="hidden" id="branch_id" name="branch_id" value="{{ auth()->user()->branch_id }}">
    @endif


    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="department_id" name="department_id" required>
            <option value=""
                    {{ isset($requestDetail) ? '' : 'selected' }} disabled>{{ __('index.select_department') }}</option>
        </select>
        @error('department_id')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id" required>
            <option value=""
                    {{ isset($requestDetail) ? '' : 'selected' }} disabled>{{ __('index.select_employee') }}</option>
        </select>
        @error('employee_id')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="loan_id" class="form-label">{{ __('index.loan') }} <span style="color: red">*</span></label>
        <select class="form-select" id="loan_id" name="loan_id" required>
            <option value=""
                    {{ isset($requestDetail) ? '' : 'selected' }} disabled>{{ __('index.select_loan') }}</option>
        </select>
        @error('loan_id')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="settlement_type" class="form-label">{{ __('index.settlement_type') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="settlement_type" name="settlement_type" required>
            <option selected disabled>{{ __('index.select_settlement_type') }}</option>
            <option
                value="partial"{{ ((isset($requestDetail) && $requestDetail->settlement_type == 'partial') || old('settlement_type') == 'partial') ? 'selected' : '' }}>
                Partial
            </option>
            <option
                value="full"{{ ((isset($requestDetail) && $requestDetail->settlement_type == 'full') || old('settlement_type') == 'full') ? 'selected' : '' }}>
                Full
            </option>
        </select>
        @error('settlement_type')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="settlement_method" class="form-label">{{ __('index.settlement_method') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="settlement_method" name="settlement_method" required>
            <option selected disabled>{{ __('index.select_settlement_method') }}</option>
            <option
                value="manual"{{ ((isset($requestDetail) && $requestDetail->settlement_method == 'manual') || old('settlement_type') == 'manual') ? 'selected' : '' }}>
                Manual
            </option>
            <option
                value="salary"{{ ((isset($requestDetail) && $requestDetail->settlement_method == 'salary') || old('settlement_type') == 'salary') ? 'selected' : '' }}>
                Salary
            </option>
        </select>
        @error('settlement_method')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="amount" class="form-label">{{ __('index.amount') }}</label>
       <input type="number" name="amount" id="amount" class="form-control" value="{{  $requestDetail->amount ?? old('amount') }}">
        @error('amount')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-lg-6 mb-4">
        <label for="tinymceDescription" class="form-label">{{ __('index.reason') }} <span
                style="color: red">*</span></label>
        <textarea name="reason" id="tinymceDescription" required rows="3">
            {!! isset($requestDetail) ? $requestDetail->reason : old('reason') !!}
        </textarea>
        @error('reason')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>


    <div class="text-start">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="plus"></i>
            {{ isset($requestDetail) ? __('index.update') : __('index.create') }}
        </button>
    </div>
</div>

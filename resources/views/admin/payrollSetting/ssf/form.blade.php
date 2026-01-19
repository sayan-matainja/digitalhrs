<div class="row">
    <div class="d-md-flex gap-4">
        <span class="text-warning d-block mb-3">* {{ __('index.ssf_calculation_message') }} *</span>
        <span class="text-warning mb-3">* Set applicable date as per the fiscal year start*</span>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="office_contribution" class="form-label">{{ __('index.office_contribution') }}</label>
        <input type="number" oninput="validity.valid||(value='');" class="form-control" step="0.1" id="office_contribution" name="office_contribution" value="{{ ( $ssfDetail ? $ssfDetail->office_contribution: old('office_contribution') )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_contribution" class="form-label">{{ __('index.employee_contribution') }}</label>
        <input type="number" oninput="validity.valid||(value='');" class="form-control" id="employee_contribution" name="employee_contribution" value="{{ ($ssfDetail? $ssfDetail->employee_contribution: old('employee_contribution') )}}" autocomplete="off" placeholder="">
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="applicable_date" class="form-label">{{ __('index.applicable_date') }}</label>
        @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
            <input type="text" class="form-control nepaliDate" id="applicable_date" name="applicable_date" value="{{ ($ssfDetail? $ssfDetail->applicable_date: $applicableDate )}}">
        @else
            <input type="date" class="form-control" id="applicable_date" name="applicable_date" value="{{ ($ssfDetail? $ssfDetail->applicable_date: $applicableDate )}}">
        @endif
    </div>

    <div class="col-lg-4 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" {{ isset($ssfDetail) ? '' :'selected' }} disabled>{{ __('index.select_status') }}</option>
            <option value="1" @selected( old('is_active',isset($ssfDetail) && $ssfDetail->is_active ) == 1)>{{ __('index.active') }}</option>
            <option value="0" @selected( old('is_active',isset($ssfDetail) && $ssfDetail->is_active ) == 0)>{{ __('index.inactive') }}</option>
        </select>
    </div>
    <div class="col-lg-12 mb-4">
        <input type="checkbox" name="enable_tax_exemption" value="1" @if(isset($ssfDetail) && $ssfDetail->enable_tax_exemption == 1) checked @endif>
        {{ __('index.enable_tax_exemption') }}
        <span class="ms-2" data-bs-toggle="tooltip" data-bs-trigger="hover focus" data-bs-placement="top" title="When enabled, the system adjusts the tax calculation by excluding the first tax bracket (i.e., the basic exemption range). This means income tax is applied starting from the next range. If SSF is disabled, tax is calculated under the standard rules, beginning from the first tax bracket as usual.">
            <i class="link-icon" data-feather="help-circle" aria-label="Tax exemption help" role="img"></i>
        </span>
    </div>

    @can('ssf')
        <div class="col-lg-12 text-start">
            <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ $ssfDetail ? __('index.update') : __('index.save') }}    {{ __('index.ssf') }}</button>
        </div>
    @endcan
</div>

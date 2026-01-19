<div class="row">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option selected disabled>{{ __('index.select_branch') }}</option>
                @foreach($branch as $value)
                    <option
                        value="{{ $value->id }}" {{ ((isset($officeTime) && $officeTime->branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : '' }}>
                        {{ ucfirst($value->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="shift" class="form-label">{{ __('index.shift') }} <span style="color: red">*</span></label>
        <input type="text" min="0" class="form-control" id="holiday_count" name="shift"
               value="{{ ( isset($officeTime) ? $officeTime->shift: old('shift') )}}" autocomplete="off"
               placeholder="{{__('index.enter_shift_name')}}">


    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="shift_type" class="form-label">{{ __('index.type') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="shift_type" required>
            <option value="" {{isset($officeTime) ? '': 'selected'}} disabled>{{ __('index.select_shift') }}</option>
            @foreach($shifts as $type)
                <option
                    value="{{ $type->value }}" {{ (isset($officeTime) && ($officeTime->shift_type ) == $type->value) ? 'selected':old('shift_type') }} >{{ ucfirst($type->name) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="shift" class="form-label">{{ __('index.category') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="category">
            <option value="" disabled>{{ __('index.select_category') }}</option>
            @foreach($category as $value)
                <option
                    value="{{ $value }}" {{ (isset($officeTime) && ($officeTime->category ) == $value) ? 'selected':old('category') }} >{{ removeSpecialChars($value) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <label for="opening_time" class="form-label">{{ __('index.opening_time') }} <span
                style="color: red">*</span></label>
        <input type="time" class="form-control" id="opening_time" name="opening_time" required
               value="{{ ( isset($officeTime) ? convertTimeFormat($officeTime->opening_time): old('opening_time') )}}"
               autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="closing_time" class="form-label">{{ __('index.closing_time') }} <span
                style="color: red">*</span></label>
        <input type="time" class="form-control" id="closing_time" name="closing_time" required
               value="{{ ( isset($officeTime) ? convertTimeFormat($officeTime->closing_time): old('closing_time') )}}"
               autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="halfday_mark_time" class="form-label">{{ __('index.halfday_mark_time') }} <span
                style="color: red">*</span></label>
        <input type="time" class="form-control" id="halfday_mark_time" name="halfday_mark_time" required
               value="{{ ( isset($officeTime) ? convertTimeFormat($officeTime->halfday_mark_time): old('halfday_mark_time') )}}"
               autocomplete="off" placeholder="">
    </div>
</div>

<div class="checkin_rules mt-3">
    <h5 class="mb-4 border-bottom pb-3">{{ __('index.checkin_checkout_rule') }} </h5>
    <div class="row late_rule">
        <div class="col-lg-6">
            <div class="row">

                <div class="col-lg-12 mb-3">
                    <span class="form-check form-switch">
                        <input id="is_early_check_in" type="checkbox"
                               @if( (isset($officeTime) && $officeTime->is_early_check_in == 1) || (old('is_early_check_in') == 1) ) checked
                               @endif name="is_early_check_in" value="1"
                               class="form-check-input change-status-toggle">
                        <label for="is_early_check_in" class="form-label">{{ __('index.early_check_in') }} </label>
                    </span>
                </div>
                <div
                    class="col-lg-12 mb-3 @if( isset($officeTime) && $officeTime->is_early_check_in == 1 ) @else d-none @endif"
                    id="earlyCheckIn">
                    <label for="checkin_before" class="form-label">{{ __('index.check_in_before') }} </label>
                    <input type="number" id="before_start" class="form-control numeric" name="checkin_before"
                           value="{{ ( isset($officeTime) ? $officeTime->checkin_before: old('checkin_before') )}}"
                           placeholder="{{ __('index.enter_check_in_before') }}">
                    <span class="text-danger"></span>
                </div>
                <div class="col-lg-12 mb-3">
                    <span class="form-check form-switch">
                        <input id="is_early_check_out" type="checkbox"
                               @if( (isset($officeTime) && $officeTime->is_early_check_out == 1) || (old('is_early_check_out') == 1) ) checked
                               @endif name="is_early_check_out" value="1"
                               class="form-check-input change-status-toggle">
                        <label for="is_early_check_out" class="form-label">{{ __('index.early_check_out') }} </label>
                    </span>
                </div>

                <div
                    class="col-lg-12 mb-3 @if( isset($officeTime) && $officeTime->is_early_check_out == 1 ) @else d-none @endif"
                    id="earlyCheckOut">
                    <label for="checkout_before" class="form-label">{{ __('index.check_out_before') }}</label>
                    <input type="number" id="checkout_before" class="form-control numeric" name="checkout_before"
                           value="{{ isset($officeTime) ? $officeTime->checkout_before : old('checkout_before') }}"
                           placeholder="{{ __('index.enter_check_out_before') }}">
                    <span class="text-danger"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="row">
                <div class="col-lg-12 mb-3">
                    <span class="form-check form-switch">
                        <input id="is_late_check_in" type="checkbox"
                               @if( (isset($officeTime) && $officeTime->is_late_check_in == 1) || (old('is_late_check_in') == 1) ) checked
                               @endif name="is_late_check_in" value="1"
                               class="form-check-input change-status-toggle">
                        <label for="is_late_check_in" class="form-label">{{ __('index.late_check_in') }}</label>
                    </span>
                </div>

                <div
                    class="col-lg-12 mb-3 @if( isset($officeTime) && $officeTime->is_late_check_in == 1 ) @else d-none @endif"
                    id="lateCheckIn">
                    <label for="checkin_after" class="form-label">{{ __('index.check_in_after') }}</label>
                    <input type="number" id="checkin_after" class="form-control numeric" name="checkin_after"
                           value="{{ isset($officeTime) ? $officeTime->checkin_after : old('checkin_after') }}"
                           placeholder="{{ __('index.enter_check_in_after') }}">
                    <span class="text-danger"></span>
                </div>
                <div class="col-lg-12 mb-3">
                    <span class="form-check form-switch">
                        <input id="is_late_check_out" type="checkbox"
                               @if( (isset($officeTime) && $officeTime->is_late_check_out == 1) || (old('is_late_check_out') == 1) ) checked
                               @endif name="is_late_check_out" value="1"
                               class="form-check-input change-status-toggle">
                        <label for="is_late_check_out" class="form-label">{{ __('index.late_check_out') }}</label>
                    </span>
                </div>
                <div
                    class="col-lg-12 mb-3 @if( isset($officeTime) && $officeTime->is_late_check_out == 1 ) @else d-none @endif"
                    id="lateCheckOut">
                    <label for="checkout_after" class="form-label">{{ __('index.check_out_after') }}</label>
                    <input type="number" id="checkout_after" class="form-control numeric" name="checkout_after"
                           value="{{ isset($officeTime) ? $officeTime->checkout_after : old('checkout_after') }}"
                           placeholder="{{ __('index.enter_check_out_after') }}">
                    <span class="text-danger"></span>
                </div>
            </div>
        </div>

    </div>

    <div class="text-start">
        <button type="submit" class="btn btn-primary"><i class="link-icon"
                                                         data-feather="{{isset($officeTime)? 'edit-2':'plus'}}"></i> {{isset($officeTime)? __('index.update'):__('index.create')}}
            {{ __('index.office_time') }}
        </button>
    </div>
</div>

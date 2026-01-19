<div class="row">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-6 col-md-6 mb-3">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-control" id="branch_id" name="branch_id" required>
                <option selected disabled>{{ __('index.select_branch') }}</option>
                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        <option value="{{$branch->id}}" {{ isset($underTime) && $underTime->branch_id == $branch->id ? 'selected' :'' }}>
                            {{ucfirst($branch->name)}}</option>
                    @endforeach
                @endif
            </select>
        </div>
    @endif
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="title" class="form-label"> {{ __('index.title') }} <span style="color: red">*</span></label>
        <input type="text"
               class="form-control"
               id="title" step="0.1" min="0" name="title" required
               value="{{ isset($underTime) ? $underTime->title: old('title') }}"
               autocomplete="off"
               placeholder="{{ __('index.enter_title') }}">
        @error('title')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="applied_after_minutes" class="form-label"> {{ __('index.undertime_after_minute') }} <span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="applied_after_minutes" step="0.1" min="0" name="applied_after_minutes" required
               value="{{ isset($underTime) ?$underTime->applied_after_minutes: old('applied_after_minutes') }}"
               autocomplete="off"
               placeholder="{{ __('index.placeholder_ut_after') }}">
        @error('applied_after_minutes')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="penalty_type" class="form-label"> {{ __('index.penalty_type') }} <span style="color: red">*</span> </label>
        <select class="col-md-12 form-select penalty_type" id="penalty_type" name="penalty_type" required>
            <option selected disabled> {{ __('index.select_penalty_type') }}</option>
            <option {{ ((isset($underTime) && $underTime->penalty_type) == 0) ? 'selected' :'' }} value="0">{{ __('index.percent') }}</option>
            <option {{ ((isset($underTime) && $underTime->penalty_type) == 1) ? 'selected' :'' }} value="1">{{ __('index.amount') }}</option>
        </select>
    </div>
    <div class="col-lg-6 col-md-6 mb-3 penalty_percent">
        <label for="penalty_percent" class="form-label"> {{ __('index.penalty_percent') }} <span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="penalty_percent" step="0.01" min="0" name="penalty_percent"
               value="{{ isset($underTime) ? $underTime->penalty_percent: '' }}"
               autocomplete="off"
               placeholder="{{ __('index.penalty_percent_placeholder') }}">
        @error('penalty_percent')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-3 penalty_rate {{ (isset($underTime) && $underTime->penalty_type == 1) ? '' :'d-none' }}" >
        <label for="ut_penalty_rate" class="form-label">{{ __('index.penalty_rate') }}<span style="color: red">*</span></label>
        <input type="number"
               class="form-control"
               id="ut_penalty_rate" step="0.01" min="0" name="ut_penalty_rate"
               value="{{ isset($underTime) ?$underTime->ut_penalty_rate: '' }}"
               autocomplete="off"
               placeholder="{{ __('index.penalty_rate_placeholder') }}">
        @error('ut_penalty_rate')
        <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-3">
        <label for="is_active" class="form-label"> {{ __('index.status') }} <span style="color: red">*</span></label>
        <input type="radio" class="mx-2" id="is_active_1" {{ (old('is_active') || (isset($underTime) && $underTime->is_active) == 1)? 'checked' : '' }} name="is_active"  value="1">{{ __('index.active') }}

        <input type="radio" class="mx-2" id="is_active_0" {{ (old('is_active') || (isset($underTime) && $underTime->is_active) == 0)? 'checked' : '' }} name="is_active" value="0">{{ __('index.inactive') }}
    </div>
    @can('undertime_setting')
        <div class="col-12">
            <button type="submit" class="btn btn-primary ">
                <i class="link-icon" data-feather="{{ isset($underTime) ? 'edit-2':'plus'}}"></i>
                {{ isset($underTime) ? __('index.update'):__('index.save') }}
            </button>
        </div>
    @endcan
</div>

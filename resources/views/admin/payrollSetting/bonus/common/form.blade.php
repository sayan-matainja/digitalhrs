<div class="row">

    <div class="col-lg-6 col-md-6 mb-3">
        <label for="title" class="form-label"> {{ __('index.title') }} <span style="color: red">*</span></label>
        <input type="text"
               class="form-control"
               id="name" name="title" required
               value="{{ old('title') }}"
               autocomplete="off"
               placeholder="{{ __('index.enter_bonus_type') }}">
    </div>

    <div class="col-lg-6 col-md-6 mb-3">
        <label for="value_type" class="form-label">{{ __('index.value_type') }} <span style="color: red">*</span></label>
        <select class="form-select" id="value_type" name="value_type" required>
            <option selected disabled>{{ __('index.select_value_type') }}</option>
            @foreach(\App\Enum\BonusTypeEnum::cases() as $case)
                <option value="{{ $case->value }}">{{ Str::title(str_replace('_', ' ', $case->name)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 col-md-6 mb-3">
        <label for="value" class="form-label">{{ __('index.value') }}</label>
        <input type="number" min="0" step="0.1" class="form-control" id="value" name="value"
               value="{{ old('value') }}"
               autocomplete="off">
    </div>

    <div class="col-lg-6 col-md-6 mb-3">
        <label for="applicable_month" class="form-label">{{ __('index.applicable_month') }}<span style="color: red">*</span></label>
        <select class="form-select" id="applicable_month" name="applicable_month" required>
            <option value="" {{ isset($bonusDetail) || old('applicable_month') ? '' : 'selected' }} disabled>{{ __('index.select_month') }}</option>
            @foreach($months as $key=>$value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 mb-3">
        <label for="is_active" class="form-label">{{ __('index.is_active') }} <span style="color: red">*</span></label>
        <select class="form-select" id="is_active" name="is_active" required>
            <option disabled>{{ __('index.select_status') }}
            </option>
            <option value="1">{{ __('index.yes') }}</option>
            <option value="0">{{ __('index.no') }}</option>
        </select>
    </div>


    <div class="col-12">
        <button type="submit" class="btn btn-primary ">
            <i class="link-icon" data-feather="plus"></i>
             {{__('index.add')}}
        </button>
    </div>
</div>






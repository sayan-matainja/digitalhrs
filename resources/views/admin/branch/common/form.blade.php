<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.company_name') }} <span style="color: red">*</span></label>
        <select class="form-select" id="exampleFormControlSelect1" name="company_id">
            <option selected value="{{ isset($company) ? $company->id : '' }}">{{ isset($company) ? $company->name : '' }}</option>
        </select>
    </div>


    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.branch_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" required name="name" value="{{ isset($branch) ? $branch->name : '' }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.branch_head') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="branch_head_id">
            <option value="" {{ !isset($branch) ? 'selected' : '' }} disabled>{{ __('index.select_branch_head') }}</option>
            @foreach($users as $key => $user)
                <option value="{{ $user->id }}" {{ isset($branch) && $branch->branch_head_id  == $user->id ? 'selected' : '' }}>{{ ucfirst($user->name) }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="address" class="form-label">{{ __('index.address') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="address" required name="address" value="{{ isset($branch) ? $branch->address : old('address') }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="phone" class="form-label">{{ __('index.phone_number') }} <span style="color: red">*</span></label>
        <input type="number" class="form-control" id="phone" required name="phone" value="{{ isset($branch) ? $branch->phone : old('phone') }}" autocomplete="off" placeholder="">
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_location_latitude" class="form-label">{{ __('index.branch_location_latitude') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="branch_location_latitude" required name="branch_location_latitude" value="{{ isset($branch) ? $branch->branch_location_latitude : old('branch_location_latitude') }}" autocomplete="off" placeholder="{{ __('index.enter_branch_location_latitude') }}">
    </div>

     <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_location_longitude" class="form-label">{{ __('index.branch_location_longitude') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="branch_location_longitude" required name="branch_location_longitude" value="{{ isset($branch) ? $branch->branch_location_longitude : old('branch_location_longitude') }}" autocomplete="off" placeholder="{{ __('index.enter_branch_location_longitude') }}">
    </div>

    <div class="col-lg-4 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" {{ !isset($branch) ? 'selected' : '' }} disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ isset($branch) && $branch->is_active == 1 ? 'selected' : old('is_active') }}>{{ __('index.active') }}</option>
            <option value="0" {{ isset($branch) && $branch->is_active == 0 ? 'selected' : old('is_active') }}>{{ __('index.inactive') }}</option>
        </select>
    </div>

    <div class="col-lg-6 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ isset($branch) ? __('index.update') : __('index.create') }}</button>
    </div>
</div>

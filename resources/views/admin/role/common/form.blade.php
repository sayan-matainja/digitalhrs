

<div class="row">

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="name" class="form-label"> @lang('index.role_name') <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="name" required name="name" value="{{ ( isset($roleDetail) ? $roleDetail->name: '' )}}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">@lang('index.authorize_backend_login')</label>
        <select class="form-select" id="exampleFormControlSelect1" name="backend_login_authorize">
            <option value="" {{isset($roleDetail) ? '':'selected'}} >@lang('index.select_status')</option>
            <option value="1" {{ isset($roleDetail) && ($roleDetail->backend_login_authorize ) == 1 ? 'selected': old('backend_login_authorize') }}>@lang('index.yes')</option>
            <option value="0" {{ isset($roleDetail) && ($roleDetail->backend_login_authorize ) == 0 ? 'selected': old('backend_login_authorize') }}>@lang('index.no')</option>
        </select>
    </div>


    <div class="col-lg-6 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">@lang('index.status')</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value=""  disabled>@lang('index.select-status')</option>
            <option value="1" {{ isset($roleDetail) && ($roleDetail->is_active ) == 1 ? 'selected': old('is_active') }}>@lang('index.active')</option>
            <option value="0" {{ isset($roleDetail) && ($roleDetail->is_active ) == 0 ? 'selected': old('is_active') }}>@lang('index.inactive')</option>
        </select>
    </div>



    <div class="col-lg-6 col-md-6 text-start mb-4 mt-md-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{isset($roleDetail)? __('index.update'): __('index.create') }} @lang('index.role')</button>
    </div>
</div>

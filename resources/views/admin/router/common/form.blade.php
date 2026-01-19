

<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">@lang('index.branch') <span style="color: red">*</span></label>
        <select class="form-select" id="exampleFormControlSelect1" name="branch_id" required>
            <option value="" {{isset($routerDetail) ? '': 'selected'}}  disabled >@lang('index.select_branch')</option>
            @foreach($companyDetail->branches()->get() as $key => $branch)
                <option value="{{ $branch->id }}" {{ (isset($routerDetail) && ($routerDetail->branch_id ) == $branch->id)  ? 'selected': old('branch_id') }}> {{ucfirst($branch->name)}}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="router_ssid" class="form-label">@lang('index.router_bssid')  <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="router_ssid" required name="router_ssid" value="{{ ( isset($routerDetail) ? ($routerDetail->router_ssid): old('router_ssid') )}}" autocomplete="off" placeholder="00:00:00:00:00">
    </div>

    <div class="col-lg-6 text-start">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="{{isset($routerDetail)? 'edit-2':'plus'}}"></i> {{isset($routerDetail)? __('index.update'):__('index.add')}} @lang('index.router')</button>
    </div>
</div>

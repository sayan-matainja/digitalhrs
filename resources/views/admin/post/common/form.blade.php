<div class="row align-items-center">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">

                <option {{isset($postDetail) ? '' : 'selected'}}  disabled>{{ __('index.select_branch') }}</option>

                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        <option value="{{$branch->id}}"
                            {{ (isset($postDetail) && ($postDetail->branch_id ) == $branch->id) ? 'selected': '' }}>
                            {{ucfirst($branch->name)}}</option>
                    @endforeach
                @endif
            </select>
        </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department_label') }} <span style="color: red">*</span></label>
        <select class="form-select" id="department_id" name="dept_id" required>
            <option selected disabled>{{ __('index.select_department') }}</option>

        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.post_name_label') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="post_name" required name="post_name" value="{{ isset($postDetail) ? $postDetail->post_name : '' }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="exampleFormControlSelect1" class="form-label">{{ __('index.status_label') }}</label>
        <select class="form-select" id="exampleFormControlSelect1" name="is_active">
            <option value="" disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ isset($postDetail) && $postDetail->is_active == 1 ? 'selected' : old('is_active') }}>{{ __('index.active_option') }}</option>
            <option value="0" {{ isset($postDetail) && $postDetail->is_active == 0 ? 'selected' : old('is_active') }}>{{ __('index.inactive_option') }}</option>
        </select>
    </div>

    <div class="col-lg-4 mb-4 mt-lg-4">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="plus"></i>
            {{ isset($postDetail) ? __('index.update') : __('index.create') }}
        </button>
    </div>
</div>

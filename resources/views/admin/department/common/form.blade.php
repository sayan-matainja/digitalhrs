<div class="row align-items-center">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select"  name="branch_id" id="branch_id" required>
            <option {{ !isset($departmentsDetail) ? 'selected' : '' }} disabled>{{ __('index.select_branch') }}</option>

            @foreach($branches as $key => $branch)
                <option value="{{ $branch->id }}" {{( isset($departmentsDetail) && $departmentsDetail->branch_id  ==
                    $branch->id)  ? 'selected' : ''}}>{{ ucfirst($branch->name) }}</option>
            @endforeach

        </select>
    </div>
    @endif
    @if(!auth('admin')->check() && auth()->check())
        <input type="hidden" disabled readonly id="branch_id" name="branch_id" value="{{ auth()->user()->branch_id }}">
    @endif
    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="name" class="form-label">{{ __('index.department_name') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="dept_name" required name="dept_name" value="{{ isset($departmentsDetail) ? $departmentsDetail->dept_name : '' }}" autocomplete="off" placeholder="">
    </div>

    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="dept_head_id" class="form-label">{{ __('index.department_head') }}</label>
        <select class="form-select" id="dept_head_id" name="dept_head_id">
            @if(isset($departmentsDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ $user->id ==  $departmentsDetail->dept_head_id ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @else
                <option selected  disabled>{{ __('index.select_department_head') }}</option>
            @endif
        </select>
    </div>



    <div class="col-xxl-4 col-xl-4 col-md-6 mb-4">
        <label for="status" class="form-label">{{ __('index.status') }}</label>
        <select class="form-select" id="status" name="is_active">
            <option value="" {{ !isset($departmentsDetail) ? 'selected' : '' }} disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ isset($departmentsDetail) && $departmentsDetail->is_active == 1 ? 'selected' : old('is_active') }}>{{ __('index.active') }}</option>
            <option value="0" {{ isset($departmentsDetail) && $departmentsDetail->is_active == 0 ? 'selected' : old('is_active') }}>{{ __('index.inactive') }}</option>
        </select>
    </div>

    <div class="col-lg-12 mb-4">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ isset($departmentsDetail) ? __('index.update_department') : __('index.create_department') }}</button>
    </div>
</div>

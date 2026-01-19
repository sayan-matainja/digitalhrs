

<div class="row align-items-center">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branches as $value)
                <option
                    value="{{ $value->id }}" {{ ((isset($qrData) && $qrData->branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

{{--    <div class="col-lg-4 col-md-6 mb-4">--}}
{{--        <label for="department_id" class="form-label">{{ __('index.department') }} <span--}}
{{--                style="color: red">*</span></label>--}}
{{--        <select class="form-select" id="department_id" name="department_id">--}}
{{--            @if(isset($qrData))--}}
{{--                @foreach($filteredDepartment as $department)--}}
{{--                    <option--}}
{{--                        value="{{ $department->id }}" {{ $department->id ==  $qrData->department_id ? 'selected' : '' }}>--}}
{{--                        {{ ucfirst($department->dept_name) }}--}}
{{--                    </option>--}}
{{--                @endforeach--}}
{{--            @else--}}
{{--                <option selected disabled>{{ __('index.select_department') }}</option>--}}
{{--            @endif--}}
{{--        </select>--}}
{{--    </div>--}}

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="name" class="form-label"> @lang('index.title') <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title" required name="title" value="{{ (old('title') || isset($qrData) ? $qrData->title : '' ) }}" autocomplete="off" placeholder="QR Title">
    </div>

    <div class="col-lg-4 col-md-6 mb-4 mt-lg-4 text-start">
        <button type="submit" class="btn btn-primary"><i class="link-icon" data-feather="plus"></i> {{ isset($qrData) ? __('index.update') : __('index.create') }} @lang('index.qr')</button>
    </div>
</div>

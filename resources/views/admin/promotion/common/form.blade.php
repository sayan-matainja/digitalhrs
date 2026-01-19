@php use App\Helpers\AppHelper; @endphp
<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option
                    value="{{ $value->id }}" {{ ((isset($promotionDetail) && $promotionDetail->branch_id == $value->id) || (isset(auth()->user()->branch_id) && auth()->user()->branch_id == $value->id)) ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>

    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="department_id" name="department_id">
            @if(isset($promotionDetail))
                @foreach($filteredDepartment as $department)
                    <option
                        value="{{ $department->id }}" {{ $department->id ==  $promotionDetail->department_id ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_department') }}</option>
            @endif
        </select>
    </div>

    <div class="col-lg-4 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id">

            @if(isset($promotionDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ $user->id ==  $promotionDetail->employee_id ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_employee') }}</option>
            @endif
        </select>
    </div>
    <div class="col-lg-4 mb-4">
        <label for="old_post_id" class="form-label">{{ __('index.post') }} <span style="color: red">*</span></label>
        <select class="form-select" id="old_post_id" name="old_post_id">
            @if(isset($promotionDetail))
                <option selected value="{{ $promotionDetail->old_post_id }}">
                    {{ $promotionDetail->oldPost->post_name }}
                </option>

            @endif
        </select>
    </div>

</div>
<div class="promotion_section">
    <h5 class="border-top pt-3 mb-4"> {{ __('index.promotion_section') }}</h5>
    <div class="promotion_detail">
        <div class="row">
            <div class="col-lg-6 d-md-flex d-block gap-3 d-lg-block">
                <div class="post_name mb-4 w-100">
                    <label for="post_id" class="form-label">{{ __('index.post') }} <span style="color: red">*</span></label>
                    <select class="form-select" id="post_id" name="post_id">

                        @if(isset($promotionDetail))
                            @foreach($filteredPosts as $post)
                                <option value="{{ $post->id }}" {{ $post->id ==  $promotionDetail->post_id ? 'selected' : '' }}>
                                    {{ ucfirst($post->post_name) }}
                                </option>
                            @endforeach
                        @else
                            <option selected disabled>{{ __('index.select_post') }}</option>
                        @endif
                    </select>
                </div>
                <div class="promotion-date mb-4 w-100">
                    <label for="promotion_date" class="form-label">@lang('index.promotion_date') <span
                            style="color: red">*</span> </label>
                    @if($isBsEnabled)
                        <input type="text" class="form-control nepali_date" id="promotion_date" name="promotion_date" required
                            value="{{ ( isset( $promotionDetail) ?  AppHelper::taskDate($promotionDetail->promotion_date): old('promotion_date') )}}"
                            autocomplete="off">
                    @else
                        <input type="date" class="form-control" name="promotion_date" required
                            value="{{ ( isset( $promotionDetail) ?  $promotionDetail->promotion_date: old('promotion_date') )}}"
                            autocomplete="off">
                    @endif
                </div>

            </div>
            <div class="col-lg-6 mb-4">
                <label for="tinymceExample" class="form-label">{{ __('index.description') }}</label>
                <textarea class="form-control" name="description" id="tinymceExample"
                        rows="1">{{ ( isset($promotionDetail) ? $promotionDetail->description: old('description') )}}</textarea>
            </div>
        </div>
    </div>

{{--    <div class="col-lg-4">--}}

{{--        <div class="mb-4 w-100">--}}
{{--            <label for="status" class="form-label">@lang('index.status')</label>--}}
{{--            <select class="form-select" id="status" name="status">--}}
{{--                @foreach($status as $stat)--}}
{{--                    <option--}}
{{--                        value="{{ $stat->value }}" {{  isset($promotionDetail) && $stat->value ==  $promotionDetail->status ? 'selected' : '' }}>--}}
{{--                        {{ ucfirst($stat->value) }}--}}
{{--                    </option>--}}
{{--                @endforeach--}}
{{--            </select>--}}
{{--        </div>--}}
{{--    </div>--}}



    <input type="hidden" readonly id="notification" name="notification" value="0">

    @canany(['edit_promotion','create_promotion'])
        <div class="text-center text-md-start border-top pt-4">
            <button type="submit" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($promotionDetail)?  __('index.update'): __('index.create')}}
            </button>

            <button type="submit" id="withNotification" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($promotionDetail)?  __('index.update_send'): __('index.create_send')}}
            </button>
        </div>
    @endcanany
</div>





<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">

            <option {{isset($awardDetail) ? '' : 'selected'}}  disabled>{{ __('index.select_branch') }}</option>

            @if(isset($companyDetail))
                @foreach($companyDetail->branches()->get() as $key => $branch)
                    <option value="{{$branch->id}}"
                        {{ (isset($awardDetail) && ($awardDetail->branch_id ) == $branch->id) ? 'selected': '' }}>
                        {{ucfirst($branch->name)}}</option>
                @endforeach
            @endif
        </select>
    </div>
    @endif
    @if(!auth('admin')->check() && auth()->check())
        <input type="hidden" id="branch_id" name="branch_id" value="{{ auth()->user()->branch_id }}">
    @endif

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="department_id" name="department_id">

            @if(isset($awardDetail))
                @foreach($filteredDepartment as $department)
                    <option
                        value="{{ $department->id }}" {{ $department->id ==  $awardDetail->department_id ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_department') }}</option>
            @endif


        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id">
            @if(isset($awardDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ $user->id ==  $awardDetail->employee_id ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @else
                <option selected  disabled>{{ __('index.select_employee') }}</option>
            @endif

        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="award_type_id" class="form-label">{{ __('index.award_name') }} <span style="color: red">*</span></label>
        <select class="form-select" id="award_type_id" name="award_type_id" required>
            @if(isset($awardDetail))
                @foreach($filteredTypes as $type)
                    <option
                        value="{{ $type->id }}" {{ $type->id ==  $awardDetail->award_type_id ? 'selected' : '' }}>
                        {{ ucfirst($type->title) }}
                    </option>
                @endforeach
            @else
                <option selected disabled>{{ __('index.select_award_type') }}</option>
            @endif

        </select>
    </div>


    <div class="col-lg-4 col-md-6 mb-4">
        <label for="gift_item" class="form-label">{{ __('index.gift_item') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="gift_item"
               name="gift_item"
               value="{{ (isset($awardDetail) ? $awardDetail->gift_item: old('gift_item') )}}"
               required autocomplete="off"
               placeholder="{{ __('index.enter_gift_item') }}">
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="award_base" class="form-label">{{ __('index.award_base') }} <span style="color: red">*</span></label>
        <select class="form-select" id="award_base" name="award_base" required>
            <option value="" {{isset($awardDetail) ? '': 'selected'}}  disabled>{{ __('index.select_award_base') }}</option>
            @foreach($awardBases as $key =>  $value)
                <option value="{{$value->value}}" {{ isset($awardDetail) && ($awardDetail->award_base ) == $value->value || old('award_base') == $value->value ? 'selected': '' }}>
                    {{ucfirst($value->name)}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="awarded_date" class="form-label">{{ __('index.awarded_date') }} <span style="color: red">*</span></label>
        @if(\App\Helpers\AppHelper::ifDateInBsEnabled())
            <input type="text"
                   class="form-control awarded_date"
                   id="awarded_date_np"
                   name="awarded_date"
                   value="{{ ( isset($awardDetail) ? \App\Helpers\AppHelper::taskDate($awardDetail->awarded_date): old('awarded_date') )}}"
                   required
                   autocomplete="off" >
        @else
            <input type="date"
                   class="form-control"
                   id="awarded_date"
                   name="awarded_date"
                   value="{{ ( isset($awardDetail) ? ($awardDetail->awarded_date): old('awarded_date') )}}"
                   required
                   autocomplete="off" >
        @endif

    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="awarded_by" class="form-label">{{ __('index.awarded_by') }}</label>
        <input type="text" class="form-control"
               id="awarded_by"
               name="awarded_by"
               value="{{ (isset($awardDetail) ? $awardDetail->awarded_by: old('awarded_by') )}}"
               autocomplete="off"
               placeholder="{{ __('index.enter_awarded_by') }}">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="note" class="form-label">{{ __('index.award_description') }}</label>
        <textarea class="form-control" name="award_description" id="tinymceExample" rows="1">{{ ( isset($awardDetail) ? $awardDetail->award_description: old('award_description') )}}</textarea>
    </div>
    <div class="col-lg-6 mb-4">
        <label for="note" class="form-label">{{ __('index.gift_description') }}</label>
        <textarea class="form-control" name="gift_description" id="tinymceExample" rows="1">{{ ( isset($awardDetail) ? $awardDetail->gift_description: old('gift_description') )}}</textarea>
    </div>
        <div class="col-lg-6 col-md-6 mb-4">
            <label for="attachment" class="form-label">{{ __('index.upload_attachments') }} </label>
            <input class="form-control"
                   type="file"
                   id="attachment"
                   name="attachment"
                   accept=".jpeg,.png,.jpg,.webp"
                   value="{{ isset($awardDetail) ? $awardDetail->attachment : old('attachment') }}"
            >
            <img class="mt-3 {{(isset($awardDetail) && $awardDetail->attachment) ? '': 'd-none'}}"
                 id="image-preview"
                 src="{{ (isset($awardDetail) && $awardDetail->attachment) ? asset(\App\Models\Award::UPLOAD_PATH.$awardDetail->attachment) : ''}}"
                 style="object-fit: contain"
                 width="200"
                 height="200"
            >
        </div>

        <div class="col-lg-6 col-md-6 mb-4">
            <label for="awarded_by" class="form-label">{{ __('index.reward_code') }}</label>
            <input type="text" class="form-control"
                   id="reward_code"
                   name="reward_code"
                   value="{{ (isset($awardDetail) ? $awardDetail->reward_code : $rewardCode )}}"
                   readonly autocomplete="off"
                   placeholder="{{ __('index.enter_reward_code') }}">
        </div>
    @canany(['edit_award','create_award'])
        <div class="text-start">
            <button type="submit" class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>
                {{ isset($awardDetail)?  __('index.update'): __('index.create')}}
            </button>
        </div>
    @endcanany
</div>




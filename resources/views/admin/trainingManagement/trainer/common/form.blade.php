
<div class="row">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option selected disabled>{{ __('index.select_branch') }}</option>
                @foreach($branch as $key =>  $value)
                    <option value="{{ $value->id }}" {{ isset($trainerDetail) && ($trainerDetail->branch_id == $value->id ) ? 'selected' : '' }}>
                        {{ ucfirst($value->name) }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="trainer_type" class="form-label">{{ __('index.trainer_type') }} <span style="color: red">*</span></label>
        <select class="form-select" id="trainer_type" name="trainer_type" required>
            <option value="" {{isset($trainerDetail) ? '': 'selected'}}  disabled>{{ __('index.select_trainer_type') }}</option>
            @foreach($trainerTypes as $key =>  $value)
                <option value="{{$value->value}}" {{ isset($trainerDetail) && ($trainerDetail->trainer_type ) == $value->value || old('trainer_type') == $value->value ? 'selected': '' }}>
                    {{ucfirst($value->name)}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4 internalTrainer {{ isset($trainerDetail) && ($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::internal->value) ? '' : 'd-none' }}">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
        <select class="form-select" id="department_id" name="department_id" >
            <option  selected disabled>{{ __('index.select_department') }}</option>

        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4 internalTrainer {{ isset($trainerDetail) && ($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::internal->value) ? '' : 'd-none' }}">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id">
            <option  selected disabled>{{ __('index.select_employee') }}</option>

        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4 externalTrainer {{ isset($trainerDetail) && ($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::external->value) ? '' : 'd-none' }}">
        <label for="name" class="form-label">{{ __('index.name') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="name"
               name="name"
               value="{{ (isset($trainerDetail) ? $trainerDetail->name: old('name') )}}"
               autocomplete="off"
               placeholder="{{ __('index.name') }}">
    </div>
    <div class="col-lg-4 col-md-6 mb-4 externalTrainer {{ isset($trainerDetail) && ($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::external->value) ? '' : 'd-none' }}">
        <label for="email" class="form-label">{{ __('index.email') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="email"
               name="email"
               value="{{ (isset($trainerDetail) ? $trainerDetail->email: old('email') )}}"
               autocomplete="off"
               placeholder="{{ __('index.email') }}">
    </div>
    <div class="col-lg-4 col-md-6 mb-4 externalTrainer {{ isset($trainerDetail) && ($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::external->value) ? '' : 'd-none' }}">
        <label for="contact_number" class="form-label">{{ __('index.contact_number') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="contact_number"
               name="contact_number"
               value="{{ (isset($trainerDetail) ? $trainerDetail->contact_number: old('contact_number') )}}"
               autocomplete="off"
               placeholder="{{ __('index.contact_number') }}">
    </div>

    <div class="col-lg-4 col-md-6 mb-4 externalTrainer {{ isset($trainerDetail) && ($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::external->value) ? '' : 'd-none' }}">
        <label for="expertise" class="form-label">{{ __('index.expertise') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="expertise"
               name="expertise"
               value="{{ (isset($trainerDetail) ? $trainerDetail->expertise: old('expertise') )}}"
               autocomplete="off"
               placeholder="{{ __('index.expertise') }}">
    </div>
    <div class="col-lg-4 col-md-6 mb-4 externalTrainer {{ isset($trainerDetail) && ($trainerDetail->trainer_type == \App\Enum\TrainerTypeEnum::external->value) ? '' : 'd-none' }}">
        <label for="address" class="form-label">{{ __('index.address') }}<span style="color: red">*</span></label>
        <input type="text" class="form-control"
               id="address"
               name="address"
               value="{{ (isset($trainerDetail) ? $trainerDetail->address: old('address') )}}"
               autocomplete="off"
               placeholder="{{ __('index.address') }}">
    </div>


@canany(['update_trainer','create_trainer'])
        <div class="text-start">
            <button type="submit" class="btn btn-primary">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($trainerDetail)?  __('index.update'): __('index.create')}}
            </button>
        </div>
    @endcanany
</div>




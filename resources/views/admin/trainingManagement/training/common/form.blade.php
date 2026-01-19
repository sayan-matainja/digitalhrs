
<div class="row">
    @if(!isset(auth()->user()->branch_id))
    <div class="col-lg-4 col-md-6 mb-4 internalTrainer">
        <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
        <select class="form-select" id="branch_id" name="branch_id">
            <option selected disabled>{{ __('index.select_branch') }}</option>
            @foreach($branch as $value)
                <option value="{{ $value->id }}" {{ (isset($trainingDetail) && $trainingDetail->branch_id == $value->id)  ? 'selected' : '' }}>
                    {{ ucfirst($value->name) }}
                </option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4 internalTrainer">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span style="color: red">*</span></label>
        <select class="form-select" id="department_id" multiple name="department_id[]">
            @if(isset($trainingDetail))
                @foreach($filteredDepartment as $department)
                    <option value="{{ $department->id }}" {{ in_array($department->id, $departmentIds) ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="employee_id" class="form-label">{{ __('index.employee') }} <span style="color: red">*</span></label>
        <select class="form-select" id="employee_id" name="employee_id[]" multiple>
            @if(isset($trainingDetail))
                @foreach($filteredUsers as $user)
                    <option value="{{ $user->id }}" {{ in_array($user->id, $employeeIds) ? 'selected' : '' }}>
                        {{ ucfirst($user->name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <label for="training_type_id" class="form-label">{{ __('index.training_type') }} <span style="color: red">*</span></label>
        <select class="form-select" id="training_type_id" name="training_type_id" required>
            <option {{isset($trainingDetail) ? '': 'selected'}}  disabled>{{ __('index.select_training_type') }}</option>

        </select>
    </div>



    <div class="col-lg-4 col-md-6 event-date-time mb-3">
        <label for="start_date" class="form-label">@lang('index.start_date') <span style="color: red">*</span> </label>
        @if($isBsEnabled)
            <input type="text" class="form-control nepali_date" id="start_date" name="start_date" required value="{{ ( isset( $trainingDetail) ?  \App\Helpers\AppHelper::taskDate($trainingDetail->start_date): old('start_date') )}}"
                   autocomplete="off" >
        @else
            <input type="date" class="form-control" name="start_date" required value="{{ ( isset( $trainingDetail) ?  $trainingDetail->start_date: old('start_date') )}}"
                   autocomplete="off" >
        @endif
    </div>
    <div class="col-lg-4 col-md-6 event-date-time mb-3">
        <label for="end_date" class="form-label">@lang('index.end_date') </label>
        @if($isBsEnabled)
            <input type="text" class="form-control nepali_date" id="end_date" name="end_date"  value="{{ ( isset( $trainingDetail->end_date) ?  \App\Helpers\AppHelper::taskDate($trainingDetail->end_date): old('end_date') )}}"
                   autocomplete="off" >
        @else
            <input type="date" class="form-control" name="end_date" value="{{ ( isset( $trainingDetail) ?  $trainingDetail->end_date: old('end_date') )}}"
                   autocomplete="off" >
        @endif
    </div>
    <div class="col-lg-3 col-md-6 event-date-time mb-3">
        <label for="start_time" class="form-label">@lang('index.start_time') <span style="color: red">*</span> </label>
        <input type="time" class="form-control" id="start_time" name="start_time" required value="{{ ( isset( $trainingDetail) ? $trainingDetail->start_time : old('start_time') )}}"
               autocomplete="off" >
    </div>
    <div class="col-lg-3 col-md-6 event-date-time mb-3">
        <label for="end_time" class="form-label">@lang('index.end_time') <span style="color: red">*</span> </label>
        <input type="time" class="form-control" id="end_time" name="end_time" required value="{{ ( isset( $trainingDetail) ?$trainingDetail->end_time : old('end_time') )}}"
               autocomplete="off" >
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="cost" class="form-label"> {{ __('index.cost') }}</label>
        <input type="number" class="form-control" id="cost" name="cost" min="0" step="0.1" oninput="validity.valid||(value='');" value="{{ ( isset( $trainingDetail) ?  $trainingDetail->cost: old('cost') )}}"
               autocomplete="off" placeholder="{{ __('index.cost') }}">
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <label for="venue" class="form-label"> {{ __('index.venue') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="venue" name="venue" value="{{ ( isset( $trainingDetail) ?  $trainingDetail->venue: old('venue') )}}"
               autocomplete="off" placeholder="{{ __('index.venue') }}">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="tinymceExample" class="form-label">{{ __('index.description') }}</label>
        <textarea class="form-control" name="description" id="tinymceExample" rows="1">{{ ( isset($trainingDetail) ? $trainingDetail->description: old('tinymceExample') )}}</textarea>
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="certificate" class="form-label">{{ __('index.certificate') }} </label>
        <input class="form-control"
               type="file"
               id="certificate"
               name="certificate"
               accept=".jpeg,.png,.jpg,.webp"
               value="{{ isset($trainingDetail) ? $trainingDetail->certificate : old('certificate') }}"
        >
        <img class="mt-3 {{(isset($trainingDetail) && $trainingDetail->certificate) ? '': 'd-none'}}"
             id="image-preview"
             src="{{ (isset($trainingDetail) && $trainingDetail->certificate) ? asset(\App\Models\Training::UPLOAD_PATH.$trainingDetail->certificate) : ''}}"
             style="object-fit: contain"
             width="200"
             height="200"
        >
    </div>


    <div class="col-lg-12 mb-4">
        <div class="trainer-add d-flex align-items-center justify-content-between border-top pt-4">
            <h5> {{ __('index.assign_trainer_section') }}</h5>
            <button type="button" class="btn btn-sm btn-success float-end" id="add-section-btn">{{ __('index.assign_trainer') }}</button>
        </div>
    </div>

    <div id="training-section-container">

        @if(isset($trainingDetail))
        @foreach($trainingDetail->trainingInstructor as $index => $instructor)
            <div class="training-section row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <label for="trainer_type_{{ $index }}" class="form-label">{{ __('index.trainer_type') }} <span style="color: red">*</span></label>
                    <select class="form-select trainer_type" id="trainer_type_{{ $index }}" name="trainer_type[{{ $index }}]" required>
                        <option value="" disabled>{{ __('index.select_trainer_type') }}</option>
                        @foreach($trainerTypes as $key => $value)
                            <option value="{{ $value->value }}"
                                {{ $value->value == $instructor->trainer_type ? 'selected' : '' }}>
                                {{ ucfirst($value->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <label for="trainer_id_{{ $index }}" class="form-label">{{ __('index.trainer') }} <span style="color: red">*</span></label>
                    <select class="form-select trainer_id" id="trainer_id_{{ $index }}" name="trainer_id[{{ $index }}]" required>
                        <option value="" disabled>{{ __('index.select_trainer') }}</option>
                        @foreach($instructor->trainer->where('trainer_type', $instructor->trainer_type)->get() as $trainer)
                            <option value="{{ $trainer->id }}"
                                {{ $trainer->id == $instructor->trainer_id ? 'selected' : '' }}>
                                {{ $trainer->name ?? $trainer->employee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <button type="button" class="btn btn-danger remove-section-btn" style="margin-top: 30px;">x</button>
                </div>
            </div>

        @endforeach
        @else
        @endif
    </div>


    <input type="hidden" readonly id="notification" name="notification" value="0">

@canany(['edit_training','create_training'])
        <div class="text-center text-md-start border-top pt-4">
            <button type="submit" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($trainingDetail)?  __('index.update'): __('index.create')}}
            </button>

            <button type="submit" id="withNotification" class="btn btn-primary mb-2">
                <i class="link-icon" data-feather="plus"></i>
                {{isset($trainingDetail)?  __('index.update_send'): __('index.create_send')}}
            </button>
        </div>
    @endcanany
</div>




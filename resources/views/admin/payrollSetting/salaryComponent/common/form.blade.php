<div class="row">
    <div class="col-lg-12 mb-3"> <span class="text-danger">* {{__('index.applicable_to_tax_report')}} </span> </div>

    <div class="col-lg-6 mb-3">
        <label for="title" class="form-label"> {{ __('index.name') }} <span style="color: red">*</span></label>
        <input type="text"
               class="form-control"
               id="name" name="name" required
               value="{{ ( isset($salaryComponentDetail) ?  $salaryComponentDetail->name: old('name') )}}"
               autocomplete="off"
               placeholder="{{ __('index.enter_salary_component_name') }}">
    </div>

    <div class="col-lg-6 mb-3">
        <label for="component_type" class="form-label">{{ __('index.component_type') }} <span style="color: red">*</span></label>
        <select class="form-select" id="component_type" name="component_type" required >
            <option value="" {{isset($salaryComponentDetail) || old('component_type') ? '' : 'selected'}}  disabled>{{ __('index.select_component_type') }}</option>
            @foreach(\App\Models\SalaryComponent::COMPONENT_TYPE as $key => $value)
                <option value="{{$key}}"
                    {{ isset($salaryComponentDetail) && ($salaryComponentDetail->component_type ) == $key || old('component_type') == $key ? 'selected': '' }}>
                    {{ucfirst($value)}}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 mb-3">
        <label for="value_type" class="form-label">{{ __('index.value_type') }}<span style="color: red">*</span></label>
        <select class="form-select" id="value_type" name="value_type" required>
            <option value="" {{ isset($salaryComponentDetail) || old('value_type') ? '' : 'selected' }} disabled>{{ __('index.select_value_type') }}</option>
            @foreach(\App\Enum\SalaryComponentTypeEnum::cases() as $case)
                <option value="{{ $case->value }}"
                    {{ (isset($salaryComponentDetail) && $salaryComponentDetail->value_type == $case->value) || old('value_type') == $case->value ? 'selected' : '' }}>
                    {{ Str::title(str_replace('_', ' ', $case->name)) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 mb-3 @if((isset($salaryComponentDetail) && $salaryComponentDetail->value_type == \App\Enum\SalaryComponentTypeEnum::adjustable->value)) d-none @endif">
        <label for="annual_component_value" class="form-label">{{ __('index.component_value_annual') }}</label>
        <input type="number" min="0" step="0.1" class="form-control" id="annual_component_value" name="annual_component_value"
               value="{{ isset($salaryComponentDetail) ? $salaryComponentDetail->annual_component_value : old('annual_component_value') }}"
               autocomplete="off">
    </div>



    <div class="col-lg-6 mb-3">
        <input type="checkbox" name="apply_for_all" value="1"
               @if(isset( $salaryComponentDetail) &&  $salaryComponentDetail->apply_for_all == 1) checked @endif
        > {{ __('index.apply_for_all') }}
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary ">
            <i class="link-icon" data-feather="{{isset($salaryComponentDetail)? 'edit-2':'plus'}}"></i>
            {{isset($salaryComponentDetail) ?  __('index.update'): __('index.add')}}
        </button>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        function toggleComponentValueDiv() {
            let valueType = document.getElementById('value_type').value;
            let adjustableValue = '{{ \App\Enum\SalaryComponentTypeEnum::adjustable->value }}';

            if (valueType !== adjustableValue) {
                document.getElementById('annual_component_value').parentElement.classList.remove('d-none');
            } else {
                document.getElementById('annual_component_value').parentElement.classList.add('d-none');
            }
        }

        // Call the function on page load to set the initial state
        toggleComponentValueDiv();

        // Attach the function to the onchange event
        document.getElementById('value_type').addEventListener('change', toggleComponentValueDiv);
    });
</script>





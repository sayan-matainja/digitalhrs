<?php

namespace App\Requests\Payroll\SalaryComponent;

use App\Enum\SalaryComponentTypeEnum;
use App\Models\SalaryComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalaryComponentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'component_type' => ['required','string', Rule::in(array_keys(SalaryComponent::COMPONENT_TYPE))],
            'value_type' => ['required',Rule::in(array_column(SalaryComponentTypeEnum::cases(), 'value'))],
            'annual_component_value' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf(function () {
                    return $this->value_type != SalaryComponentTypeEnum::adjustable->value;
                })
            ],
            'apply_for_all'=>['nullable',Rule::requiredIf(function () {
                return request()->get('value_type') == SalaryComponentTypeEnum::adjustable->value;
            })]
        ];
        if ($this->isMethod('put')) {
            $rules['name'] = ['required','string','unique:salary_components,name,'.$this->salary_component];
        } else {
            $rules['name'] = ['required','string',Rule::unique('salary_components','name')];
        }
        return $rules;

    }

}

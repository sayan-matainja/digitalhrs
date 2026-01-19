<?php

namespace App\Requests\Payroll\SalaryGroup;


use App\Models\SalaryGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalaryGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function prepareForValidation()
    {

        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }

    public function rules(): array
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|array|min:1',
            'department_id.*' => 'required|exists:departments,id',
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('salary_groups', 'name')->ignore($this->route('salary_group'))
            ],
            'salary_component_id' => ['required', 'array', 'min:1'],
            'salary_component_id.*' => [
                'nullable',
                Rule::exists('salary_components', 'id')->where('status', true)
            ],
            'employee_id' => ['required', 'array'],
            'employee_id.*' => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where('is_active', true)
                    ->where('status', 'verified'),
                Rule::unique('salary_group_employees', 'employee_id')->ignore($this->route('salary_group'), 'salary_group_id')
            ],
        ];
    }

    public function messages()
    {
        return [
          'salary_group_employee.*.unique' =>  'The employee cannot be in more than one group.'
        ];
    }

}

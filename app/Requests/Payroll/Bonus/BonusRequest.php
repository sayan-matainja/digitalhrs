<?php

namespace App\Requests\Payroll\Bonus;

use App\Enum\BonusTypeEnum;
use App\Models\SalaryComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BonusRequest extends FormRequest
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
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'value_type' => ['required', Rule::in(array_column(BonusTypeEnum::cases(), 'value'))],
            'value' => ['required','numeric','min:0'],
            'is_active'=>['nullable'],
            'apply_for_all'=>['nullable','boolean'],
            'applicable_month'=>['nullable'],
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required|exists:users,id',
            'department_id' => 'required|array|min:1',
            'department_id.*' => 'required|exists:departments,id',
        ];
        if ($this->isMethod('put')) {
            $rules['title'] = [
                'required',
                'string',
                Rule::unique('bonuses', 'title')->ignore($this->bonu)
            ];
        } else {
            $rules['title'] = ['required', 'string', Rule::unique('bonuses', 'title')];
        }
        return $rules;

    }

}

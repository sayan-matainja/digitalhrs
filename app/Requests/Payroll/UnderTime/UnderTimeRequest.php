<?php

namespace App\Requests\Payroll\UnderTime;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnderTimeRequest extends FormRequest
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


    public function rules()
    {
        return [
            'branch_id' => 'required|exists:branches,id',
            'title' => ['nullable'],
            'applied_after_minutes' => ['required', 'numeric', 'gt:0'],
            'penalty_type' => ['required','in:0,1'],
            'ut_penalty_rate' => ['nullable','required_if:penalty_type,1','numeric','gt:0'],
            'penalty_percent' => ['nullable','required_if:penalty_type,0','numeric','gt:0'],
            'is_active' => ['required', 'boolean', Rule::in([1, 0])],
        ];

    }
    public function messages(): array
    {
        return [
            'applied_after_minutes.gt'  => 'UnderTime Applied After must be greater than 0',
            'overtime_pay_rate.gt' => 'UnderTime Penalty Rate must be greater than 0',
        ];
    }
}

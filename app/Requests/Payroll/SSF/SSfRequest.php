<?php

namespace App\Requests\Payroll\SSF;

use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SSfRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    public function prepareForValidation()
    {
        $startDate =  AppHelper::getEnglishDate($this->input('applicable_date'));

        $fromDate = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate);

        $this->merge([
            'applicable_date' => $fromDate->format('Y-m-d'),
        ]);

    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
            'office_contribution' => [
                'required_if:is_active,1',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'employee_contribution' => [
                'required_if:is_active,1',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'applicable_date' => 'required|date',
            'enable_tax_exemption'=>'nullable|in:1,0',
        ];

    }
    public function messages()
    {
        return [
            'is_active.boolean' => 'The status must be either active or inactive.',
            'is_active.in' => 'The status must be either active or inactive.',

            'office_contribution.required_if' => 'The office contribution to PF is required when the SSF is active.',
            'office_contribution.numeric' => 'The office contribution to PF must be a number.',
            'office_contribution.min' => 'The office contribution to PF must be at least 0.',
            'office_contribution.regex' => 'The office contribution to PF must have up to 2 decimal places.',

            'employee_contribution.required_if' => 'The salary contribution to PF is required when the SSF is active.',
            'employee_contribution.numeric' => 'The salary contribution to PF must be a number.',
            'employee_contribution.min' => 'The salary contribution to PF must be at least 0.',
            'employee_contribution.regex' => 'The salary contribution to PF must have up to 2 decimal places.',

        ];
    }

}










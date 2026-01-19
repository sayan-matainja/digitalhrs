<?php

namespace App\Requests\Payroll\AdvanceSalary;

use App\Repositories\UserRepository;
use App\Rules\AdvanceSalaryAmountRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvanceSalaryRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'requested_amount' => ['required','numeric','gt:100', new AdvanceSalaryAmountRule()],
            'description' => ['required','string'],
            'documents' => ['nullable', 'array', 'min:1'],
            'documents.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,docx,doc,xls,pdf', 'max:5048'],
        ];
        $rules['advance_salary_id'] = ['sometimes', Rule::exists('advance_salaries', 'id')->where('status', 'pending')];

        return $rules;
    }

    public function messages()
    {
        return [
          'advance_salary_id' => 'Updatable advance salary request data not found'
        ];
    }
}

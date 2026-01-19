<?php

namespace App\Requests\Payroll\SalaryTDS;

use App\Models\SalaryTDS;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalaryTDSUpdateRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'marital_status' => ['required',Rule::in(SalaryTDS::MARITAL_STATUS)],
            'annual_salary_from' => ['required','numeric','min:0'],
            'annual_salary_to' => ['required','numeric','gt:annual_salary_from'],
            'tds_in_percent' => ['required','numeric']
        ];
    }

}

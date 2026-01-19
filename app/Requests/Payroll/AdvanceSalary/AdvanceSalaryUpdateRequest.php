<?php

namespace App\Requests\Payroll\AdvanceSalary;

use App\Models\AdvanceSalary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvanceSalaryUpdateRequest extends FormRequest
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
        return [
            'status' => ['required',Rule::in(AdvanceSalary::STATUS)],
            'released_amount' => ['nullable','numeric','gt:99'],
            'documents' => ['sometimes','array','min:1'],
            'documents.*.' => ['sometimes','file','mimes:jpeg,png,jpg,docx,doc,xls,pdf','max:5048'],
            'remark' => ['nullable','required_if:status,rejected,approved','string'],
        ];
    }
}

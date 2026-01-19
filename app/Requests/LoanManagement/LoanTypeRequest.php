<?php

namespace App\Requests\LoanManagement;

use App\Enum\LoanInterestTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanTypeRequest extends FormRequest
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
        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'branch_id' => ['required','exists:branches,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('asset_types')->ignore($this->asset_type)],
            'minimum_amount' => ['required', 'numeric', 'min:0'],
            'maximum_amount' => ['required', 'numeric', 'min:0','gt:minimum_amount'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'interest_type' => ['required','string', Rule::in(array_column(LoanInterestTypeEnum::cases(), 'value'))],
            'term' => ['required', 'numeric', 'min:1'],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

    }

}


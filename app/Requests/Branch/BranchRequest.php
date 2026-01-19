<?php

namespace App\Requests\Branch;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
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
            'name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            'branch_head_id' => 'nullable|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'branch_location_latitude' => 'required|numeric',
            'branch_location_longitude' => 'required|numeric',
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

    }

}











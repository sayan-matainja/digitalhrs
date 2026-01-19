<?php

namespace App\Requests\Support;

use App\Models\Department;
use App\Models\Support;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupportStoreRequest extends FormRequest
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
        if($this->route()->getPrefix() === 'api'){
            $this->merge([
                'branch_id' => auth()->user()?->branch_id,
            ]);
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
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'branch_id' => ['required', Rule::exists('branches','id')],
            'department_id' => ['required', Rule::exists('departments','id')
                ->where('is_active',Department::IS_ACTIVE)
            ],
            'status' => ['nullable',Rule::in(Support::STATUS)],
        ];

    }
}

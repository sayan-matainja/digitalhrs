<?php

namespace App\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
        $this->merge([
            'name' => Str::lower($this->string('name')),
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
            'name' => ['required','string',Rule::unique('roles','name')->ignore($this->role)],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
            'backend_login_authorize' => ['required', 'boolean', Rule::in([1, 0])],
        ];
    }

}














<?php

namespace App\Requests\User\Api;


use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserProfileUpdateApiRequest extends FormRequest
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
            'name' => 'nullable|string|max:100|min:2',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore(getAuthUserCode())],
            'address' => 'nullable|string|max:100',
            'dob' => 'nullable|date|date_format:Y-m-d|before:today',
            'phone' => 'nullable|numeric',
            'gender' => ['nullable', 'string', Rule::in(User::GENDER)],
            'avatar' => ['nullable'],
        ];

    }

}

















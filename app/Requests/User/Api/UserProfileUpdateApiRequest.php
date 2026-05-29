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
            'name' => 'sometimes|string|max:100|min:2',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore(getAuthUserCode())],
            'address' => 'sometimes|string|max:100',
            'dob' => 'sometimes|date|date_format:Y-m-d|before:today',
            'phone' => 'sometimes|numeric',
            'gender' => ['sometimes', 'string', Rule::in(User::GENDER)],
            'avatar' => ['sometimes'],
        ];

    }

}

















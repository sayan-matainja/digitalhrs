<?php

namespace App\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserLeaveTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'leave_type_id.*' => 'nullable',
            'days.*' => 'nullable|numeric|gte:0',
            'is_active.*' => 'nullable',

        ];

    }
    public function messages()
    {
        return [
            'days.*.numeric' => 'Each leave day must be a valid number.',
            'days.*.gte' => 'Each leave days must be greater than or equal to 0.',
        ];
    }

}
















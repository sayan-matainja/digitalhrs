<?php

namespace App\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'project_id' => ['required', Rule::exists('projects','id')],
            'employee' => ['required', 'array', 'min:1'],
            'employee.*' => ['required',
                Rule::exists('users', 'id')
                    ->where('is_active', 1)

            ],
        ];

    }

}


<?php

namespace App\Requests\Task;

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
            'task_id' => ['required', Rule::exists('tasks','id')],
            'employee' => ['required', 'array', 'min:1'],
            'employee.*' => ['required',
                Rule::exists('users', 'id')
                    ->where('is_active', 1)

            ],
        ];

    }

}


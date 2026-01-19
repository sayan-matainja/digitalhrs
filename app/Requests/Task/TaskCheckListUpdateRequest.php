<?php

namespace App\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskCheckListUpdateRequest extends FormRequest
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
        $rules = [
            'task_id' =>['required',Rule::exists('tasks','id')
                ->where('is_active',1)
            ],
            'name' => ['required', 'string'],
            'assigned_to' => ['required_with:name',
                Rule::exists('assigned_members','member_id')
                    ->where('assignable_id',$this->task_id)
                    ->where('assignable_type','task')
            ],
            'is_completed' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'task_id' => 'Task not available or currently is in inactive phase.'
        ];
    }
}

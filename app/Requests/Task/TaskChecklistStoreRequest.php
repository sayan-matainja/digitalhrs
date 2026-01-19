<?php

namespace App\Requests\Task;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskChecklistStoreRequest extends FormRequest
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
        $taskId = $this->task_id;
        $rules = [
            'task_id' =>['required',Rule::exists('tasks','id')->where('is_active',1)],
            'name' => ['required', 'array', 'min:1'],
            'name.*' => ['required', 'string', 'max:500'],
            'assigned_to' => ['required', 'array', 'min:1'],
            'assigned_to.*' => ['required_with:name.*',
                Rule::exists('assigned_members','member_id')
                    ->where('assignable_id',$taskId)
                    ->where('assignable_type','task')
            ],
            'is_completed' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

        return $rules;

    }

}


<?php

namespace App\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskCommentRequest extends FormRequest
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
            'task_id' => ['required',Rule::exists('tasks','id')
                ->where('is_active',1)
            ],
            'comment_id' => ['nullable',Rule::exists('task_comments','id')],
            'description' => ['required','string'],
            'mentioned' => ['nullable', 'array', 'min:1'],
            'mentioned.*' => ['required_if:mentioned,!=,null',
                  Rule::exists('assigned_members','member_id')
                ->where('assignable_id',$taskId)
                ->where('assignable_type','task')
            ],
        ];
        return $rules;

    }
}

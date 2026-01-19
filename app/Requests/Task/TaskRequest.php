<?php

namespace App\Requests\Task;

use App\Helpers\AppHelper;
use App\Models\Project;
use App\Rules\TaskDateRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
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
        if(AppHelper::ifDateInBsEnabled()){
            $startDate = AppHelper::getEnglishDate($this->start_date);

            $endDate = AppHelper::getEnglishDate($this->end_date);

            $this->merge([
                'start_date' => date("Y-m-d H:i", strtotime($startDate . ' ' .$this->start_time)),
                'end_date' => date("Y-m-d H:i", strtotime($endDate . ' ' .$this->end_time)),
            ]);
        }else{
            $this->merge([
                'start_date' => date("Y-m-d H:i", strtotime($this->start_date)),
                'end_date' => date("Y-m-d H:i", strtotime($this->end_date)),
            ]);
        }

        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }

        if (!$this->branch_id) {
            $branch = Project::where('id',$this->project_id)->first()->branch_id;
            $this->merge(['branch_id' => $branch]);
        }


    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $projectId = $this->project_id;
        $rules = [
            'name' => ['required','string','max:400'],
            'branch_id' => ['required',
                Rule::exists('branches','id')
            ],
            'project_id' => ['required',
                Rule::exists('projects','id')
                    ->where('is_active',1)
            ],
            'priority' => ['nullable',Rule::in(Project::PRIORITY)],
            'status' => ['nullable',Rule::in(Project::STATUS)],
            'end_date' => ['required','date','date_format:Y-m-d H:i','after:start_date',new TaskDateRule($this->project_id)],
            'description' => ['required','string'],
            'assigned_member' => ['required','array','min:1'],
            'assigned_member.*' => ['required',
                Rule::exists('assigned_members','member_id')
                    ->where('assignable_id',$projectId)
                    ->where('assignable_type','project')
            ],

            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
            'attachments' => ['sometimes','array','min:1'],
            'attachments.*.' => ['sometimes','file','mimes:pdf,jpeg,png,jpg,docx,doc,xls,txt,webp,zip','max:5048'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'notification'=> 'nullable',

        ];

        if($this->isMethod('put')) {
            $rules['start_date'] = ['required','date','date_format:Y-m-d H:i',new TaskDateRule($this->project_id)];
        } else {
            $rules['start_date'] = ['required','date','date_format:Y-m-d H:i','after_or_equal:today',new TaskDateRule($this->project_id)];
        }

        return $rules;

    }

}

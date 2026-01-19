<?php

namespace App\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectAttachmentRequest extends FormRequest
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
            'project_id' => ['required',Rule::exists('projects','id')->where('is_active',1)],
            'attachments' => ['required','array','min:1'],
            'attachments.*.' => ['required','file','mimes:pdf,jpeg,png,jpg,docx,doc,xls,txt,webp,zip','max:5048']
        ];
        return $rules;

    }
}

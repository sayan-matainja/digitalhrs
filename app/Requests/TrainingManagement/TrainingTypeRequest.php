<?php

namespace App\Requests\TrainingManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingTypeRequest extends FormRequest
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
        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'status' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];

        $uniqueRule = Rule::unique('training_types', 'title')
            ->where('branch_id', $this->branch_id);

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $uniqueRule = $uniqueRule->ignore($this->route('training_type'));

            $rules['title'] = ['required', 'string', $uniqueRule];
        } else {
            $rules['title'] = ['required', 'string', $uniqueRule];
        }
        return $rules;

    }

}


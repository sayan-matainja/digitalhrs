<?php

namespace App\Requests\Leave;

use App\Enum\LeaveGenderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
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
        $rules =  [
            'branch_id' => ['required','exists:branches,id'],
            'early_exit' => ['sometimes', 'boolean', Rule::in([1, 0])],
            'leave_paid' => ['nullable', 'boolean', Rule::in([1, 0])],
            'leave_allocated' => ['nullable','required_if:leave_paid,1','numeric','min:1'],
            'gender' => ['required',Rule::in(array_column(LeaveGenderEnum::cases(), 'value'))],
        ];
        $uniqueRule = Rule::unique('leave_types', 'name')
            ->where('branch_id', $this->branch_id);

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $uniqueRule = $uniqueRule->ignore($this->route('leave_type'));

            $rules['name'] = ['required', 'string', $uniqueRule];
        } else {
            $rules['name'] = ['required', 'string', $uniqueRule];
        }

        return $rules;
    }

}
















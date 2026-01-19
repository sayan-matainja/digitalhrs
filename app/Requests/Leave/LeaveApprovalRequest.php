<?php

namespace App\Requests\Leave;

use App\Enum\LeaveApproverEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveApprovalRequest extends FormRequest
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
        return [
            'subject' => ['required'],
            'leave_type_id' => ['required'],
            'branch_id' => ['required','exists:branches,id'],
            'department_id' => ['required', 'array', 'min:1'],
            'department_id.*' => [
                'required',
                Rule::exists('departments', 'id')->where('is_active', 1)
            ],

            'approver' => ['required', 'array', 'min:1'],
            'approver.*' => ['required', 'in:' . implode(',', array_column(LeaveApproverEnum::cases(), 'value'))],

            'role_id.*' => ['nullable', 'exists:roles,id'],
            'user_id.*' => ['nullable', 'exists:users,id'],
        ];


        $approvers = $this->input('approver') ?: [];

        foreach ($approvers as $index => $approver) {
            if ($approver === 'specific_personnel') {
                $rules["role_id.$index"] = ['required', 'exists:roles,id'];
                $rules["user_id.$index"] = ['required', 'exists:users,id'];
            }
        }

        return $rules;
    }
    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $approvers = $this->input('approver', []);
            $userIds = $this->input('user_id', []);

            foreach ($approvers as $index => $approver) {
                if ($approver === 'specific_personnel') {
                    if (empty($userIds[$index] ?? null)) {
                        $validator->errors()->add("user_id.$index", "Employee is required when approver is Specific Personnel.");
                    }
                    if (empty($this->input("role_id.$index"))) {
                        $validator->errors()->add("role_id.$index", "Role is required when approver is Specific Personnel.");
                    }
                }
            }
        });
    }


}
















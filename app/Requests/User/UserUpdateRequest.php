<?php
namespace App\Requests\User;

use App\Helpers\AppHelper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
        $this->merge([
            'joining_date' => $this->input('joining_date') ? AppHelper::getEnglishDate($this->input('joining_date')) : null,
            'dob' => $this->input('dob') ? AppHelper::getEnglishDate($this->input('dob')) : null,
        ]);
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
            // Name fields - Split into three
            'name' => 'nullable', // Keep for backward compatibility
            'surname' => 'required|string|max:100|min:2',
            'first_name' => 'required|string|max:100|min:2',
            'middle_name' => 'nullable|string|max:100',

            'email' => ['required', 'email', Rule::unique('users')->ignore($this->employee)],
            'username' => ['required', 'string', Rule::unique('users')->ignore($this->employee)],
            'address' => 'nullable|required_unless:role_id,1',
            'dob' => 'nullable|required_unless:role_id,1|date|before:today',
            'phone' => 'nullable|required_unless:role_id,1|numeric',
            'nin' => 'required|numeric',
            'gender' => ['nullable', 'required_unless:role_id,1', 'string', Rule::in(User::GENDER)],
            'marital_status' => ['nullable', 'required_unless:role_id,1', 'string', Rule::in(User::MARITAL_STATUS)],
            'employment_type' => ['nullable', 'required_unless:role_id,1', 'string', Rule::in(User::EMPLOYMENT_TYPE)],
            'joining_date' => 'nullable|date|before_or_equal:today',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|required_unless:role_id,1|exists:branches,id',
            'department_id' => 'nullable|required_unless:role_id,1|exists:departments,id',
            'post_id' => 'nullable|required_unless:role_id,1|exists:posts,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'office_time_id' => 'nullable|required_unless:role_id,1|exists:office_times,id',

            // New Company Details fields
            'grade_level' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'sbu_code' => 'nullable|string|max:50',
            'rsa_no' => 'nullable|string|max:50',
            'hmo_id' => 'nullable|string|max:50',

            'leave_allocated' => 'nullable|numeric|gte:0',
            'remarks' => 'nullable|string|max:1000',
            'workspace_type' => ['nullable', 'boolean', Rule::in([1, 0])],
            'avatar' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,svg', 'max:5048'],
            'employee_code' => ['nullable'],
            'allow_holiday_check_in' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'required_unless' => 'The :attribute field is required',
        ];
    }

    public function attributes()
    {
        return [
            'surname' => 'surname',
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'dob' => 'date of birth',
            'phone' => 'phone number',
            'nin' => 'NIN',
            'gender' => 'gender',
            'marital_status' => 'marital status',
            'employment_type' => 'employment type',
            'branch_id' => 'branch',
            'department_id' => 'department',
            'post_id' => 'post',
            'office_time_id' => 'office time',
            'grade_level' => 'grade level',
            'tax_id' => 'tax ID',
            'sbu_code' => 'SBU code',
            'rsa_no' => 'RSA number',
            'hmo_id' => 'HMO ID',
        ];
    }
}

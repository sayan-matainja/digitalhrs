<?php

namespace App\Requests\AwardManagement;

use App\Helpers\AppHelper;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AwardRequest extends FormRequest
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
        $date = AppHelper::getEnglishDate($this->awarded_date);

        $this->merge([
            'awarded_date' => date("Y-m-d", strtotime($date)),
        ]);

    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'branch_id' => ['required','exists:branches,id'],
            'department_id' => ['required','exists:departments,id'],
            'employee_id' => ['required'],
            'award_type_id' => ['required'],
            'gift_item' => ['required','string'],
            'award_base' => ['nullable','string'],
            'awarded_date' => ['required'],
            'awarded_by' => ['nullable'],
            'status' => ['nullable', 'boolean', Rule::in([1, 0])],
            'award_description' => ['nullable'],
            'gift_description' => ['nullable'],
            'attachment' => ['nullable','file', 'mimes:jpeg,png,jpg,webp','max:5048'],
            'reward_code' => ['nullable','string'],
        ];

    }

    public function messages()
    {
        return [
            'employee_id.required' => 'Employee is required',
            'award_type_id.required' => 'Award Name is required',
            'gift_item.required' => 'Gift Item is required',
            'awarded_date.required' => 'Date is required',

        ];
    }

}


<?php

namespace App\Requests\Transfer;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferRequest extends FormRequest
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

        $startDate = AppHelper::getEnglishDate($this->input('transfer_date'));
        $fromDate = Carbon::createFromFormat('Y-m-d', $startDate);

        $this->merge([
            'transfer_date' => $fromDate->format('Y-m-d'),
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
        $rules = [
            'old_branch_id' => ['required','exists:branches,id'],
            'old_department_id' => ['required','exists:departments,id'],
            'branch_id' => ['required','exists:branches,id','different:old_branch_id'],
            'department_id' => ['required','exists:departments,id','different:old_department_id'],
            'employee_id' => ['required','exists:users,id'],
            'old_post_id' => ['nullable'],
            'old_supervisor_id' => ['nullable'],
            'old_office_time_id' => ['nullable'],
            'post_id' => ['required','exists:posts,id'],
            'supervisor_id' => ['required','exists:users,id'],
            'office_time_id' => ['required','exists:office_times,id'],
            'description' => ['nullable'],
            'notification'=> 'nullable',
        ];

        if($this->isMethod('put')) {
            $rules['transfer_date'] = ['required','date','date_format:Y-m-d'];
        } else {
            $rules['transfer_date'] = ['required','date','date_format:Y-m-d','after_or_equal:today'];
        }


        return $rules;
    }

    public function messages()
    {
        return [
            'old_branch_id.required' => 'The old branch field is required.',
            'old_branch_id.exists' => 'The selected old branch is invalid.',

            'old_department_id.required' => 'The old department field is required.',
            'old_department_id.exists' => 'The selected old department is invalid.',

            'branch_id.required' => 'The new branch field is required.',
            'branch_id.exists' => 'The selected new branch is invalid.',
            'branch_id.different' => 'The new branch must be different from the old branch.',

            'department_id.required' => 'The new department field is required.',
            'department_id.exists' => 'The selected new department is invalid.',
            'department_id.different' => 'The new department must be different from the old department.',

            'employee_id.required' => 'The employee field is required.',
            'employee_id.exists' => 'The selected employee is invalid.',

            'transfer_date.required' => 'The transfer date field is required.',
            'transfer_date.date' => 'The transfer date must be a valid date.',
            'transfer_date.date_format' => 'The transfer date must be in the format YYYY-MM-DD.',
            'transfer_date.after_or_equal' => 'The transfer date must be today or a future date.',
        ];
    }


}


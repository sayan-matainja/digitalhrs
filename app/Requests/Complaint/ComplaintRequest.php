<?php

namespace App\Requests\Complaint;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintRequest extends FormRequest
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
            'complaint_from' => ['required','exists:users,id'],
            'branch_id' => ['required','exists:branches,id'],
            'department_id' => 'required|array|min:1',
            'department_id.*' => 'required|exists:departments,id',
            'employee_id' => 'required|array|min:1',
            'employee_id.*' => 'required|exists:users,id',
            'message' => ['nullable'],
            'notification'=> 'nullable',
            'subject'=> 'required|string',
            'image'=> ['nullable','file', 'mimes:jpeg,png,jpg,webp,pdf','max:2048'],
        ];

//        if($this->isMethod('put')) {
//            $rules['complaint_date'] = ['required','date','date_format:Y-m-d'];
//        } else {
//            $rules['complaint_date'] = ['required','date','date_format:Y-m-d','after_or_equal:today'];
//        }
//
//
//        return $rules;
    }




}


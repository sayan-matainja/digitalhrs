<?php

namespace App\Requests\Termination;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TerminationRequest extends FormRequest
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

        $startDate = AppHelper::getEnglishDate($this->input('termination_date'));
        $fromDate = Carbon::createFromFormat('Y-m-d', $startDate);
        $noticeDate = AppHelper::getEnglishDate($this->input('notice_date'));
        $toDate = Carbon::createFromFormat('Y-m-d', $noticeDate);

        $this->merge([
            'termination_date' => $fromDate->format('Y-m-d'),
            'notice_date' => $toDate->format('Y-m-d'),
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
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'termination_type_id' => ['required','exists:termination_types,id'],
            'employee_id' => ['required','exists:users,id'],
            'notice_date' => ['nullable','date','date_format:Y-m-d','after_or_equal:today'],
            'termination_date' => ['nullable','date','date_format:Y-m-d','after:notice_date'],
            'reason' => ['required'],
            'status'=>['nullable'],
            'document' => ['nullable','file', 'mimes:jpeg,png,jpg,webp,pdf','max:2048'],

        ];
    }

}


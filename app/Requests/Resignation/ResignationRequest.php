<?php

namespace App\Requests\Resignation;

use App\Enum\ResignationStatusEnum;
use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResignationRequest extends FormRequest
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

        $startDate = AppHelper::getEnglishDate($this->input('resignation_date'));
        $fromDate = Carbon::createFromFormat('Y-m-d', $startDate);
        $lastDate = AppHelper::getEnglishDate($this->input('last_working_day'));
        $toDate = Carbon::createFromFormat('Y-m-d', $lastDate);

        $this->merge([
            'resignation_date' => $fromDate->format('Y-m-d'),
            'last_working_day' => $toDate->format('Y-m-d'),
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
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'employee_id' => ['required','exists:users,id'],
            'resignation_date' => ['required','date','date_format:Y-m-d'],
            'last_working_day' => ['required','date','date_format:Y-m-d','after:resignation_date'],
            'reason' => ['required'],
            'status'=>['nullable'],
            'document' => ['nullable','file', 'mimes:jpeg,png,jpg,webp,pdf','max:2048'],

        ];
        if ($this->isMethod('put')) {
            $rules['admin_remark'] = [
                'nullable',
                Rule::requiredIf(function () {
                    return in_array($this->status, [
                        ResignationStatusEnum::approved->value,
                        ResignationStatusEnum::onReview->value,
                        ResignationStatusEnum::cancelled->value
                    ]);
                })
            ];
        } else {
            $rules['admin_remark'] = ['nullable'];
        }

        return $rules;

    }

}


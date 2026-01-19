<?php

namespace App\Requests\Payroll\ReviseSalary;

use App\Helpers\AppHelper;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviseSalaryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function prepareForValidation()
    {

        $startDate =  AppHelper::getEnglishDate($this->input('date_from'));
//        $endDate = AppHelper::getEnglishDate($this->input('date_to'));

        $leaveFromDate = \Carbon\Carbon::createFromFormat('Y-m-d',$startDate)->setTimeFromTimeString(now()->toTimeString());
//        $leaveToDate = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate)->setTimeFromTimeString(now()->toTimeString());

        $this->merge([
            'current_date' => now()->format('Y-m-d'),
            'date_from' => $leaveFromDate->format('Y-m-d'),
//            'date_to' => $leaveToDate->format('Y-m-d'),
        ]);
    }
    public function rules()
    {
        return [
            'employee_id' => ['required', Rule::exists('users', 'id')
                ->where('is_active', UserRepository::IS_ACTIVE)
                ->where('status', UserRepository::STATUS_VERIFIED)
            ],
            'increment_percent' => ['required', 'numeric','between:0,100'],
            'increment_amount' => ['required', 'numeric', 'gt:0'],
            'remark' => ['nullable', 'string'],
            'revised_salary' => ['required', 'numeric', 'gt:0'],
//            'fiscal_year_id' => ['required'],
            'date_from' => ['nullable', 'date','after_or_equal:current_date'],
//            'date_to' => ['nullable', 'date','after:date_from'],
        ];
    }
}

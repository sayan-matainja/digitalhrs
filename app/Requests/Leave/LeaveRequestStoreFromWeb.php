<?php

namespace App\Requests\Leave;

use App\Helpers\AppHelper;
use App\Models\LeaveRequestMaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class LeaveRequestStoreFromWeb extends FormRequest
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

        $startDate =  AppHelper::getEnglishDate($this->input('leave_from'));

        $leaveFromDate = \Carbon\Carbon::createFromFormat('Y-m-d',$startDate)->setTimeFromTimeString(now()->toTimeString());


        $this->merge([

            'leave_from' => $leaveFromDate->format('Y-m-d H:i:s'),
        ]);
        if($this->input('leave_to')){
            $endDate = AppHelper::getEnglishDate($this->input('leave_to'));
            $leaveToDate = \Carbon\Carbon::createFromFormat('Y-m-d', $endDate);
            $this->merge([
                'leave_to' => $leaveToDate->format('Y-m-d'),
            ]);
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
            'leave_for' => 'required|in:full_day,half_day',
            'leave_from' => 'required|date',
            'leave_to' => 'nullable|requiredIf:leave_for,full_day|date|after_or_equal:leave_from',
            'leave_in'=>'nullable|requiredIf:leave_for,half_day|in:first_half,second_half',
            'status' => ['sometimes', 'string', Rule::in(LeaveRequestMaster::STATUS)],
            'leave_type_id' => ['required','exists:leave_types,id'],
            'reasons' => 'required|string',
            'early_exit' => ['nullable', 'boolean', Rule::in([1, 0])],
            'start_time' =>  ['nullable'],
            'end_time' =>  ['nullable'],
        ];
    }


}


















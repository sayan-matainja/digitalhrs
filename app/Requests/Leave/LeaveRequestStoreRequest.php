<?php

namespace App\Requests\Leave;

use App\Helpers\AppHelper;
use App\Models\LeaveRequestMaster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeaveRequestStoreRequest extends FormRequest
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

        if (empty($this->input('leave_for'))) {
            $this->merge([
                'leave_for' => 'full_day',
            ]);
        }
        $this->merge([
            'current_time' => now()->format('Y-m-d'),
            'leave_from' => date('Y-m-d', strtotime($this->input('leave_from'))),

        ]);

        if($this->input('leave_to')){
            $this->merge([
                'leave_to' => date('Y-m-d', strtotime($this->input('leave_to'))),
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
            'leave_from' => 'required|date|after_or_equal:current_time',
            'leave_to' => 'nullable|requiredIf:leave_for,full_day|date|after_or_equal:leave_from',
            'leave_in'=>'nullable|requiredIf:leave_for,half_day|in:first_half,second_half',
            'status' => ['sometimes', 'string', Rule::in(LeaveRequestMaster::STATUS)],
            'leave_type_id' => ['required','exists:leave_types,id'],
            'reasons' => ['required','string','min:10'],
            'early_exit' => ['nullable', 'boolean', Rule::in([1, 0])],
        ];
    }



}

















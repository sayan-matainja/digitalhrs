<?php

namespace App\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceNightTimeEditRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'night_checkin' => 'required',
            'night_checkout' => 'nullable',
            'edit_remark' => 'required|string|min:10'
        ];
    }

}














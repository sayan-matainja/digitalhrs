<?php

namespace App\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceTimeEditRequest extends FormRequest
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
            'check_in_at' => 'required|date_format:H:i',
            'check_out_at' => 'nullable|date_format:H:i|after:check_in_at',
            'edit_remark' => 'required|string|min:10'
        ];
    }

}














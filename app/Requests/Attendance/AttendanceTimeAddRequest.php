<?php

namespace App\Requests\Attendance;

use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;

class AttendanceTimeAddRequest extends FormRequest
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
            'user_id' => 'nullable',
            'attendance_date' => 'nullable',
            'check_in_at' => 'required|date_format:H:i',
            'check_out_at' => 'nullable|date_format:H:i',
            'edit_remark' => 'required|string|min:10'
        ];
    }

}














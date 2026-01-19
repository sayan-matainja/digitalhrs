<?php

namespace App\Requests\Attendance;

use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class AttendanceCheckOutRequest extends FormRequest
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
            'check_in_latitude' => ['sometimes','numeric'],
            'check_out_latitude' => ['required','numeric'],
            'check_in_longitude' => ['sometimes','numeric'],
            'check_out_longitude' => ['required','numeric'],
            'router_bssid' => ['nullable','string'],
        ];
    }


}













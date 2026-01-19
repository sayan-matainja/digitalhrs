<?php

namespace App\Requests\Attendance;


use App\Helpers\AppHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class AttendanceCheckInRequest extends FormRequest
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
            'check_in_latitude' => ['required','numeric'],
            'check_out_latitude' =>[ 'sometimes','numeric'],
            'check_in_longitude' => ['required','numeric'],
            'check_out_longitude' =>[ 'sometimes','numeric'],
            'router_bssid' => ['nullable','string'],
        ];
    }

}












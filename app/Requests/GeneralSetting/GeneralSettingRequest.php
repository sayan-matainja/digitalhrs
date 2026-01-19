<?php

namespace App\Requests\GeneralSetting;

use App\Models\GeneralSetting;
use Illuminate\Foundation\Http\FormRequest;
use App\Enum\GeneralSettingEnum;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class GeneralSettingRequest extends FormRequest
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
            'value' => 'required|string',
        ];
    }

}



<?php

namespace App\Requests\FiscalYear;

use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiscalYearRequest extends FormRequest
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
            'year' => ['required', 'string', 'max:255'],
            'start_date' => [
                'required',
            ],
            'end_date' => [
                'required',
            ],
        ];

    }

}


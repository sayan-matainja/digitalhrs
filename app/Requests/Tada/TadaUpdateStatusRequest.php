<?php

namespace App\Requests\Tada;

use App\Models\Tada;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TadaUpdateStatusRequest extends FormRequest
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
           'status' => ['required',Rule::in('accepted','rejected')],
           'remark' => ['nullable','required_if:status,rejected','string']
       ];
    }

}

<?php

namespace App\Requests\EmployeeCard;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeCardRequest extends FormRequest
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
            'title'            => 'required|string|max:191',
            'orientation'     => 'required|in:portrait,landscape',
            'extra_fields_order' => 'array',
            'extra_fields_order.*' => 'nullable|in:phone,email,department,joining_date,employee_code,blood,address',
            'front_logo'       => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'back_logo'        => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'signature_image'  => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'footer_text'      => 'nullable|string|max:500',

            'background_color'=> 'required|string',
//            'text_color'=> 'required|string',
//            'graph_color'=> 'required|string',
            'graph_type'      => 'nullable|in:none,barcode,qr',
            'graph_content'    => 'nullable|string',

            'back_title' => 'nullable|string',
            'back_text' => 'nullable|string',
            'is_active'       => 'nullable|boolean',
            'is_default'      => 'nullable|boolean',
        ];

    }

}












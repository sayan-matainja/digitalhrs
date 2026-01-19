<?php

namespace App\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string',
            'owner_name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|numeric',
            'count_holiday_weekend' => 'nullable|boolean',
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
            'website_url' => ['nullable','url'],
            'weekend' => 'nullable|array',
            'weekend.*' => 'nullable|numeric|digits_between:0,6',
        ];
        if ($this->isMethod('put')) {
            $rules['logo'] = ['sometimes','file', 'mimes:jpeg,png,jpg,gif,svg','max:5048'];
            $rules['email'] = ['required','email',Rule::unique('users')->ignore($this->id)];
        } else {
            $rules['logo'] = ['required','file', 'mimes:jpeg,png,jpg,gif,svg','max:5048'];
            $rules['email'] = [ 'required','email','unique:users,email' ];
        }
        return $rules;

    }

}










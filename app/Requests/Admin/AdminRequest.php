<?php

namespace App\Requests\Admin;



use App\Helpers\AppHelper;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
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
            'name'=>['required','string','max:100','min:2'],
            'email'=>['required','email',''.Rule::unique('admins')->ignore($this->user)],
            'username'=>['required','string',''.Rule::unique('admins')->ignore($this->user)],
            'avatar' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp','max:5048'],
        ];


        if($this->isMethod('put')) {
            $rules['password'] = ['nullable'];
        } else {
            $rules['password'] = ['required','min:6'];
        }

        return $rules;
    }



}















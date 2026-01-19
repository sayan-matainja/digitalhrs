<?php

namespace App\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
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

    public function prepareForValidation()
    {

        if (!auth('admin')->check() && auth()->check()) {
            $this->merge(['branch_id' => auth()->user()->branch_id]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules =  [
            'name' => ['required','string','max:255'],
            'email' => ['required','email', Rule::unique('clients')->ignore($this->client)],
            'contact_no' => ['required','string'],
            'address' => ['nullable','string','max:255'],
            'country' => ['required','string','max:255'],
            'is_active' => ['nullable', 'boolean', Rule::in([1, 0])],
            'branch_id' => 'required|exists:branches,id',
        ];

        if ($this->isMethod('put')) {
            $rules['avatar'] = ['sometimes', 'file', 'mimes:jpeg,png,jpg,svg','max:5048'];
        } else {
            $rules['avatar'] = ['required', 'file', 'mimes:jpeg,png,jpg,svg','max:5048'];
        }
        return $rules;

    }

}


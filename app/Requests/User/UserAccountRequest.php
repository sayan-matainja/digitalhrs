<?php

namespace App\Requests\User;

use App\Models\EmployeeAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bank_name' => 'nullable|string',
            'bank_account_no' => 'nullable|numeric',
            'bank_account_type' => ['nullable', 'string', Rule::in(EmployeeAccount::BANK_ACCOUNT_TYPE)],
            'bvn' => 'nullable|alpha_num|max:30',
            'account_holder' => 'nullable|string',
        ];

    }

}
















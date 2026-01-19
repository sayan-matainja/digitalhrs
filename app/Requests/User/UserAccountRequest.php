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
            'bank_name' => 'nullable|string|required_with:bank_account_no,bank_account_type',
            'bank_account_no' => 'nullable|numeric|required_with:bank_name,bank_account_type',
            'bank_account_type' => ['nullable', 'string', 'required_with:bank_name,bank_account_type', Rule::in(EmployeeAccount::BANK_ACCOUNT_TYPE)],
            'account_holder' => 'required|string',
        ];

    }

}
















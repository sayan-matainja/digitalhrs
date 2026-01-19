<?php

namespace App\Requests\LoanManagement;

use App\Helpers\AppHelper;
use App\Models\LoanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanRequest extends FormRequest
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
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'loan_type_id' => ['required','exists:loan_types,id'],
            'loan_id' => ['required'],
            'employee_id' => ['required', 'exists:users,id'],
            'loan_amount' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $loanTypeId = $this->input('loan_type_id');
                    if (!$loanTypeId) {
                        return;
                    }
                    $loanType = LoanType::find($loanTypeId);
                    if (!$loanType) {
                        $fail('The selected loan type is invalid.');
                        return;
                    }
                    if ($value < $loanType->minimum_amount) {
                        $fail("The {$attribute} must be at least {$loanType->minimum_amount}.");
                    }
                    if ($loanType->maximum_amount && $value > $loanType->maximum_amount) {
                        $fail("The {$attribute} must not exceed {$loanType->maximum_amount}.");
                    }
                },
            ],
            'loan_purpose' => ['required', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'description' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'monthly_installment'=> ['nullable'],
            'repayment_amount'=> ['nullable'],
        ];

    }

}


<?php

namespace App\Requests\LoanManagement;

use App\Enum\LoanRepaymentStatusEnum;
use App\Helpers\AppHelper;
use App\Models\EmployeeSalary;
use App\Models\Loan;
use App\Models\LoanType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoanSettlementRequest extends FormRequest
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
            'loan_id' => ['required', 'exists:loans,id'],
            'employee_id' => ['required', 'exists:users,id'],
            'settlement_type' => ['required', 'in:partial,full'],
            'settlement_method' => ['required', 'in:manual,salary'],
            'amount' => ['nullable', 'required_if:settlement_type,partial', 'numeric', 'min:0'],
            'reason' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Only run extra checks if validation passed so far
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $employeeId = $this->input('employee_id');
            $loanId = $this->input('loan_id');
            $settlementType = $this->input('settlement_type');
            $settlementMethod = $this->input('settlement_method');
            $amount = $this->input('amount', 0); // Default to 0 if not set

            if ($settlementMethod === 'salary') {
                $salary = EmployeeSalary::where('employee_id', $employeeId)->first();

                if (!$salary) {
                    $validator->errors()->add('settlement_method', 'This settlement by salary cannot be processed. Employee salary is not set.');
                    return;
                }

                if ($settlementType === 'partial') {
                    if ($amount > $salary->monthly_basic_salary) {
                        $validator->errors()->add('amount', 'Partial settlement amount cannot exceed employee’s monthly basic salary (' . $salary->monthly_basic_salary . ').');
                        return;
                    }
                }
            }
        });
    }
}

<?php

namespace App\Requests\LoanManagement;

use App\Enum\LoanRepaymentStatusEnum;
use App\Models\EmployeeSalary;
use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeLoanSettlementApiRequest extends FormRequest
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
            'loan_id' => ['required','exists:loans,id'],
            'settlement_type' => ['required', 'string'],
            'settlement_method' => ['required', 'string'],
            'amount'=>['nullable','required_if:settlement_type,partial'],
            'reason' => ['required', 'string'],
        ];

    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $employeeId = auth()->user()->id;
            $loanId = $this->input('loan_id');
            $settlementType = $this->input('settlement_type');
            $settlementMethod = $this->input('settlement_method');
            $amount = $this->input('amount', 0);

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


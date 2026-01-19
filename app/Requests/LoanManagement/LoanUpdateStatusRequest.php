<?php

namespace App\Requests\LoanManagement;

use App\Helpers\AppHelper;
use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enum\LoanStatusEnum;

class LoanUpdateStatusRequest extends FormRequest
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
        if (AppHelper::ifDateInBsEnabled()) {
            $this->merge([
                'repayment_from' => AppHelper::nepToEngDateInYmdFormat($this->repayment_from),
            ]);
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
            'status' => ['required', Rule::in(LoanStatusEnum::approve->value, LoanStatusEnum::reject->value)],
            'remarks' => ['nullable', 'required_if:status,' . LoanStatusEnum::reject->value, 'string', 'max:500'],
            'repayment_from' => ['nullable', 'required_if:status,' . LoanStatusEnum::approve->value, 'date', 'after_or_equal:today'],
            'payment_method_id' => ['nullable', 'required_if:status,' . LoanStatusEnum::approve->value, 'string'],
        ];
    }

}

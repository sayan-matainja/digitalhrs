<?php

namespace App\Requests\LoanManagement;

use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enum\LoanStatusEnum;

class LoanSettlementUpdateStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(LoanStatusEnum::approve->value, LoanStatusEnum::reject->value)],
            'remarks' => ['required', 'string', 'max:500'],
        ];
    }

}

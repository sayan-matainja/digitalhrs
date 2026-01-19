<?php

namespace App\Requests\TrainingManagement;

use App\Enum\TrainerTypeEnum;
use App\Helpers\AppHelper;
use App\Models\Asset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainerRequest extends FormRequest
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
            'trainer_type' => ['required',Rule::in(array_column(TrainerTypeEnum::cases(), 'value'))],
            'branch_id' => ['required'],
            'department_id' => ['nullable','required_if:shift_type,'.TrainerTypeEnum::internal->value],
            'employee_id' => ['nullable','required_if:shift_type,'.TrainerTypeEnum::internal->value],
            'name' => ['nullable','required_if:shift_type,'.TrainerTypeEnum::external->value,'string'],
            'email' => ['nullable','required_if:shift_type,'.TrainerTypeEnum::external->value,'string'],
            'contact_number' => ['nullable','required_if:shift_type,'.TrainerTypeEnum::external->value],
            'address' => ['nullable','required_if:shift_type,'.TrainerTypeEnum::external->value],
            'expertise' => ['nullable','required_if:shift_type,'.TrainerTypeEnum::external->value],
            'status'=>['nullable','boolean', Rule::in([1, 0])]
        ];

    }

}


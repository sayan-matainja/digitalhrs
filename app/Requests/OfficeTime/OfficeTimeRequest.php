<?php

namespace App\Requests\OfficeTime;

use App\Enum\ShiftTypeEnum;
use App\Models\OfficeTime;
use App\Rules\HalfDayMarkValidation;
use App\Rules\NightShiftValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\Rules\Enum;

class OfficeTimeRequest extends FormRequest
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
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => ['required','date_format:H:i',new NightShiftValidation($this->opening_time, $this->shift_type)],
            'halfday_mark_time' => ['required', 'date_format:H:i', new HalfDayMarkValidation($this->opening_time, $this->closing_time, $this->shift_type)],
            'shift' => ['required'],
            'category' => ['required',Rule::in(OfficeTime::CATEGORY)],
            'shift_type' => ['required', Rule::in(array_column(ShiftTypeEnum::cases(), 'value'))],
            'is_early_check_in' => ['nullable','required_if:shift_type,'.ShiftTypeEnum::night->value],
            'checkin_before' => ['nullable','required_if:is_early_check_in,1'],
            'is_early_check_out' => ['nullable','required_if:shift_type,'.ShiftTypeEnum::night->value],
            'checkout_before' => ['nullable','required_if:is_early_check_out,1'],
            'is_late_check_in' => ['nullable','required_if:shift_type,'.ShiftTypeEnum::night->value],
            'checkin_after' => ['nullable','required_if:is_late_check_in,1'],
            'is_late_check_out' => ['nullable','required_if:shift_type,'.ShiftTypeEnum::night->value],
            'checkout_after' => ['nullable','required_if:is_late_check_out,1'],
            'branch_id' => 'required|exists:branches,id',
        ];

    }
    public function messages()
    {
        return [
            'opening_time.required' => 'The opening time is required.',
            'opening_time.date_format' => 'The opening time must be in the format HH:MM.',
            'closing_time.required' => 'The closing time is required.',
            'closing_time.date_format' => 'The closing time must be in the format HH:MM.',
            'closing_time.after' => 'The closing time must be after the opening time.',
            'shift.required' => 'The shift is required.',
            'category.required' => 'The category is required.',
            'category.in' => 'The selected category is invalid.',
            'checkin_before.required_if' => 'The check-in before time is required when early check-in is enabled.',
            'checkout_before.required_if' => 'The check-out before time is required when early check-out is enabled.',
            'checkin_after.required_if' => 'The check-in after time is required when late check-in is enabled.',
            'checkout_after.required_if' => 'The check-out after time is required when late check-out is enabled.',
            'is_early_check_in.required_if' => 'The early checkin is required when Shift Type is'.ucfirst(ShiftTypeEnum::night->value),
            'is_early_check_out.required_if' => 'The Early check-out is required when Shift Type is'.ucfirst(ShiftTypeEnum::night->value),
            'is_late_check_in.required_if' => 'The late check-in is required when Shift Type is'.ucfirst(ShiftTypeEnum::night->value),
            'is_late_check_out.required_if' => 'The late check-out is required when Shift Type is'.ucfirst(ShiftTypeEnum::night->value),
        ];
    }

}













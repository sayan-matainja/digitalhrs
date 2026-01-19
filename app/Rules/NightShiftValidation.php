<?php

namespace App\Rules;

use App\Enum\ShiftTypeEnum;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class NightShiftValidation implements Rule
{
    protected $openingTime;
    protected $shiftType;

    /**
     * Create a new rule instance.
     *
     * @return void
     */

    public function __construct($openingTime, $shiftType)
    {
        $this->openingTime = $openingTime;
        $this->shiftType = $shiftType;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $opening = Carbon::createFromFormat('H:i', $this->openingTime);
        $closing = Carbon::createFromFormat('H:i', $value);

        if ($this->shiftType === ShiftTypeEnum::night->value) {

            return $closing->greaterThan($opening) || $closing->lt($opening->copy()->addDay());
        } else {

            return $closing->greaterThan($opening);
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The closing time must be after the opening time.';
    }
}

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

        // Check if this is an overnight shift (closing time is earlier than opening time)
        $isOvernight = $closing->lt($opening);

        if ($this->shiftType === ShiftTypeEnum::night->value) {
            // Night shifts can be overnight or same day
            return $closing->greaterThan($opening) || $closing->lt($opening->copy()->addDay());
        } else {
            // For non-night shifts: allow equal times (24-hour), same-day shifts, or overnight shifts
            return $closing->greaterThanOrEqualTo($opening) || $isOvernight;
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

<?php

namespace App\Rules;

use App\Enum\ShiftTypeEnum;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;

class HalfDayMarkValidation implements Rule
{
    protected $openingTime;
    protected $closingTime;
    protected $shiftType;

    /**
     * Create a new rule instance.
     */
    public function __construct($openingTime, $closingTime, $shiftType)
    {
        $this->openingTime = $openingTime;
        $this->closingTime = $closingTime;
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
        $openingTimeC = Carbon::createFromFormat('H:i', $this->openingTime);
        $closingTimeC = Carbon::createFromFormat('H:i', $this->closingTime);
        $halfdayTimeC = Carbon::createFromFormat('H:i', $value);

        $isNight = $this->shiftType === ShiftTypeEnum::night->value;
        $isWrap = $isNight && $closingTimeC->lt($openingTimeC);

        $openingC = Carbon::parse('2000-01-01 ' . $this->openingTime);
        $closingC = $isWrap
            ? Carbon::parse('2000-01-02 ' . $this->closingTime)
            : Carbon::parse('2000-01-01 ' . $this->closingTime);

        $halfdayC = ($isWrap && $halfdayTimeC->lt($openingTimeC))
            ? Carbon::parse('2000-01-02 ' . $value)
            : Carbon::parse('2000-01-01 ' . $value);


        if (! $halfdayC->between($openingC, $closingC)) {
            return false;
        }


        $duration = $closingC->diffInMinutes($openingC);
        $midMinutes = $duration / 2;
        $midpointC = $openingC->copy()->addMinutes($midMinutes);


        $diffMinutes = abs($halfdayC->diffInMinutes($midpointC));

        return $diffMinutes <= 60;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The halfday mark time must be between the opening and closing times and within 1 hour before or after half of the shift duration.';
    }
}

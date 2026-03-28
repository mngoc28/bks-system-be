<?php

namespace App\Rules;

use App\Models\UserTokens;
use Illuminate\Contracts\Validation\Rule;

class VisitDateRule implements Rule
{
    /**
     * @param mixed $message
     */
    protected $message;

    /**
     * Create a new rule instance.
     * @param mixed $message
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
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
        preg_match('/visit_list\.(\d+)\./', $attribute, $matches);
        $index = $matches[1] ?? null;

        if ($index === null) {
            return true;
        }

        $data = request()->input("visit_list.{$index}");
        $boardingDate = $data['boarding_date'] ?? null;
        $disembarkDate = $data['disembark_date'] ?? null;
        $boardingTime = $data['scheduled_boarding_time'] ?? null;
        $disembarkTime = $data['scheduled_disembark_time'] ?? null;

        if ($boardingDate !== $disembarkDate) {
            return true;
        }

        if ($boardingTime && $disembarkTime) {
            return strtotime($boardingTime) < strtotime($disembarkTime);
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}

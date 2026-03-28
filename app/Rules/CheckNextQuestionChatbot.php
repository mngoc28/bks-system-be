<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckNextQuestionChatbot implements Rule
{
    protected $id;
    protected $errorMessage;

    /**
     * Create a new rule instance.
     *
     * @param int $id id question
     * @param string $errorMessage error message
     * @return void
     */
    public function __construct($id, $errorMessage)
    {
        $this->id = $id;
        $this->errorMessage = $errorMessage;
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
        // If value is empty, allow
        if (empty($value)) {
            return true;
        }

        // Check if next question id is not the same as the current question id
        if ($this->id == $value) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage;
    }
}

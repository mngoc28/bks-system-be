<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckNextQuestionChatbotFlow implements Rule
{
    protected $id;
    protected $errorMessage;

    /**
     * Create a new rule instance.
     *
     * @param int $id id answer
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

        // Check if chatbot answer exists
        $chatbotAnswer = DB::table('chatbot_answers')
            ->where('id', $this->id)
            ->first();

        // If chatbot answer does not exist, return false
        if (!$chatbotAnswer) {
            return false;
        }

        // Check if next question id is not the same as the current question id
        if ($chatbotAnswer->question_id == $value) {
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

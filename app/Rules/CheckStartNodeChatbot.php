<?php

namespace App\Rules;

use App\Enums\StartNodeChatbot;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckStartNodeChatbot implements Rule
{
    protected $message;
    protected $id;

    /**
     * Create a new rule instance.
     *
     * @param string $message
     * @param int|null $id
     * @return void
     */
    public function __construct(string $message, ?int $id = null)
    {
        $this->message = $message;
        $this->id = $id;
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
        if ($value == StartNodeChatbot::YES->value) {
            $query = DB::table('chatbot_questions')
                ->where('is_start_node', StartNodeChatbot::YES->value);

            // If id is not null, then skip the current node
            if ($this->id) {
                $query->where('id', '!=', $this->id);
            }

            return !$query->exists();
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
        return $this->message;
    }
}

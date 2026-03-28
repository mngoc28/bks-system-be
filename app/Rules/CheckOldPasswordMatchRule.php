<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CheckOldPasswordMatchRule implements Rule
{
    protected ?string $oldPassword;

    /**
     * Create a new rule instance.
     *
     * @param string|null $oldPassword
     */
    public function __construct(?string $oldPassword)
    {
        $this->oldPassword = $oldPassword;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!isset($value)) {
            return false;
        }
        return Hash::check($value, Auth::guard('student')->user()->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('student.old_password_not_match');
    }
}

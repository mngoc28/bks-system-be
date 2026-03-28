<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class EmailExistsByRole implements Rule
{
    protected ?string $role;

    protected array $roleToTable = [
        'admin' => 'admins',
        'student' => 'students',
        'teacher' => 'teachers',
        'parent' => 'parents',
    ];

    /**
     * Create a new rule instance.
     *
     * @param string|null $role
     */
    public function __construct(?string $role)
    {
        $this->role = strtolower($role ?? '');
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
        if (!isset($this->roleToTable[$this->role])) {
            return false;
        }

        $table = $this->roleToTable[$this->role];

        return DB::table($table)
            ->where('email', $value)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('auth.email_not_exists', ['attribute' => 'email']);
    }
}

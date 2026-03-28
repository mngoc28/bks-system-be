<?php

namespace App\Enums;

class UserType
{
    const ADMIN   = 'admin';
    const PARTNER   = 'partner';
    const USER   = 'user';

    /**
     * Get a list of valid values
     */
    public static function getValues()
    {
        return [
            self::ADMIN,
            self::PARTNER,
            self::USER,
        ];
    }

    /**
     * Check if the value is valid
     */
    public static function isValid($value)
    {
        return in_array($value, self::getValues());
    }
}

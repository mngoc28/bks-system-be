<?php

namespace App\Enums;

class RoomType
{
    // Room types
    const SINGLE_ROOM = 1;
    const DOUBLE_ROOM = 2;
    const MINI_APARTMENT = 3;

    // Get all room type values
    public static function roomTypeValues(): array
    {
        return [self::SINGLE_ROOM, self::DOUBLE_ROOM, self::MINI_APARTMENT];
    }
}

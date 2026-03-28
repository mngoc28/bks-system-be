<?php

namespace App\Enums;

class RoomStatus
{
    // Room status
    const PRIVATE = 0;
    const PUBLIC = 1;
    public static function statusValues(): array
    {
        return [self::PRIVATE, self::PUBLIC];
    }
}

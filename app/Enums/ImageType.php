<?php

declare(strict_types=1);

namespace App\Enums;

class ImageType
{
    const OTHER = 0;
    const MAIN = 1;
    const INTERIOR = 2;
    const EXTERIOR = 3;
    const BATHROOM = 4;
    const KITCHEN = 5;
    const BALCONY = 6;
    const LIVING_ROOM = 7;
    const BEDROOM = 8;
    const DINING_ROOM = 9;
    const GARDEN = 10;
    const PARKING = 11;
    const ENTRANCE = 12;
    const STAIRCASE = 13;
    const HALLWAY = 14;
    const OFFICE = 15;

    /**
     * Get all values as array
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::OTHER,
            self::EXTERIOR,
            self::INTERIOR,
            self::BATHROOM,
            self::KITCHEN,
            self::MAIN,
            self::BALCONY,
            self::LIVING_ROOM,
            self::BEDROOM,
            self::DINING_ROOM,
            self::GARDEN,
            self::PARKING,
            self::ENTRANCE,
            self::STAIRCASE,
            self::HALLWAY,
            self::OFFICE,
        ];
    }

    /**
     * Get all names as array
     *
     * @return array
     */
    public static function names(): array
    {
        return [
            'OTHER',
            'EXTERIOR',
            'INTERIOR',
            'BATHROOM',
            'KITCHEN',
            'MAIN',
            'BALCONY',
            'LIVING_ROOM',
            'BEDROOM',
            'DINING_ROOM',
            'GARDEN',
            'PARKING',
            'ENTRANCE',
            'STAIRCASE',
            'HALLWAY',
            'OFFICE',
        ];
    }
}

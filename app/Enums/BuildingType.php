<?php

namespace App\Enums;

final class BuildingType
{
    const APARTMENT_BUILDING = 1;
    const BUILDING = 2;
    const VILLA = 3;
    const TOWNHOUSE = 4;
    const SERVICED_APARTMENT = 5;
    const BOARDING_HOUSE = 6;
    const HOTEL = 7;
    const OFFICE = 8;
    const OTHER = 9;

    public static function labels(): array
    {
        return [
            self::APARTMENT_BUILDING => "buildings.building_type.1",
            self::BUILDING => "buildings.building_type.2",
            self::VILLA => "buildings.building_type.3",
            self::TOWNHOUSE => "buildings.building_type.4",
            self::SERVICED_APARTMENT => "buildings.building_type.5",
            self::BOARDING_HOUSE => "buildings.building_type.6",
            self::HOTEL => "buildings.building_type.7",
            self::OFFICE => "buildings.building_type.8",
            self::OTHER => "buildings.building_type.9",
        ];
    }
}

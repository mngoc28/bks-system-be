<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Physical structure / venue shape (legacy enum name: PropertyType).
 *
 * Labels: __('property.structure_kind.{value}')
 */
final class PropertyStructureKind
{
    public const APARTMENT_PROPERTY = 1;

    public const PROPERTY = 2;

    public const VILLA = 3;

    public const TOWNHOUSE = 4;

    public const SERVICED_APARTMENT = 5;

    public const BOARDING_HOUSE = 6;

    public const HOTEL = 7;

    public const OFFICE = 8;

    public const OTHER = 9;

    /**
     * @return array<int, string> Translation keys for each enum value
     */
    public static function labels(): array
    {
        return [
            self::APARTMENT_PROPERTY => 'property.structure_kind.1',
            self::PROPERTY => 'property.structure_kind.2',
            self::VILLA => 'property.structure_kind.3',
            self::TOWNHOUSE => 'property.structure_kind.4',
            self::SERVICED_APARTMENT => 'property.structure_kind.5',
            self::BOARDING_HOUSE => 'property.structure_kind.6',
            self::HOTEL => 'property.structure_kind.7',
            self::OFFICE => 'property.structure_kind.8',
            self::OTHER => 'property.structure_kind.9',
        ];
    }
}

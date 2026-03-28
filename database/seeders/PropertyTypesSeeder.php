<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class PropertyTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyTypes = [
            [
                'name' => 'Apartment',
                'description' => 'Modern high-rise apartment with full facilities and elevator access, suitable for long-term stays.',
            ],
            [
                'name' => 'Studio',
                'description' => 'Open-plan studio with kitchenette and compact living space, ideal for young professionals.',
            ],
            [
                'name' => 'Villa',
                'description' => 'Private villa featuring garden or pool areas designed for family vacations and premium experiences.',
            ],
            [
                'name' => 'Homestay',
                'description' => 'Authentic homestay with host support offering local lifestyle immersion and shared amenities.',
            ],
            [
                'name' => 'Shared Room',
                'description' => 'Budget-friendly shared bedroom within a larger unit, commonly used by students or backpackers.',
            ],
            [
                'name' => 'Townhouse',
                'description' => 'Multi-storey townhouse with private entrance, typically located in urban residential neighborhoods.',
            ],
            [
                'name' => 'Penthouse',
                'description' => 'Luxury penthouse on the top floor boasting panoramic views and exclusive concierge services.',
            ],
            [
                'name' => 'Resort',
                'description' => 'Full-service resort property featuring on-site dining, spa, and recreational facilities.',
            ],
        ];

        foreach ($propertyTypes as $type) {
            PropertyType::updateOrCreate(
                ['slug' => Str::slug($type['name'])],
                [
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'icon_url' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}

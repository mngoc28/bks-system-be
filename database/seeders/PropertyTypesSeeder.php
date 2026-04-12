<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;

final class PropertyTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Deactivate existing types by default; only selected types below remain active.
        PropertyType::query()->update(['is_active' => false]);

        $propertyTypes = [
            [
                'slug' => 'khach-san-hotel',
                'name' => 'Khách sạn (Hotel)',
                'description' => 'Cơ sơ lưu trú từ 1-5 sao với đầy đủ dịch vụ buồng phòng, lễ tân và tiện ích đi kèm.',
            ],
            [
                'slug' => 'nha-nghi-motel-guesthouse',
                'name' => 'Nhà nghỉ (Motel/Guesthouse)',
                'description' => 'Cơ sở lưu trú quy mô nhỏ, cung cấp dịch vụ nghỉ ngơi cơ bản với giá cả phải chăng.',
            ],
            [
                'slug' => 'can-ho-dich-vu',
                'name' => 'Căn hộ dịch vụ',
                'description' => 'Căn hộ với các tiện ích giống khách sạn như dọn phòng, giặt là, thường dành cho chuyên gia hoặc khách lưu trú lâu.',
            ],
            [
                'slug' => 'homestay',
                'name' => 'Homestay',
                'description' => 'Trải nghiệm sống cùng người dân bản địa, tìm hiểu văn hoá và con người tại địa phương.',
            ],
        ];

        foreach ($propertyTypes as $type) {
            PropertyType::updateOrCreate(
                ['slug' => $type['slug']],
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

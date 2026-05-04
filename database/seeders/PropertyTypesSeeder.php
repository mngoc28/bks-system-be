<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PropertyTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Clear the table to ensure only the 4 specified types exist
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        PropertyType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $propertyTypes = [
            [
                'slug' => 'khach-san-hotel',
                'name' => 'Khách sạn (Hotel)',
                'description' => 'Cung cấp dịch vụ lưu trú chuyên nghiệp với đầy đủ tiện ích và phòng ốc tiêu chuẩn.',
            ],
            [
                'slug' => 'nha-nghi-guesthouse',
                'name' => 'Nhà nghỉ / Guesthouse',
                'description' => 'Cơ sở lưu trú quy mô nhỏ, tập trung vào sự tiện lợi và chi phí tiết kiệm cho khách hàng.',
            ],
            [
                'slug' => 'can-ho-dich-vu-theo-phong',
                'name' => 'Căn hộ dịch vụ cho thuê theo phòng',
                'description' => 'Mô hình căn hộ cao cấp được chia nhỏ thành các phòng riêng biệt, kết hợp tiện nghi nhà ở và dịch vụ khách sạn.',
            ],
            [
                'slug' => 'homestay-co-chia-phong',
                'name' => 'Homestay có chia phòng',
                'description' => 'Không gian sống gia đình được thiết kế chia thành nhiều phòng nghỉ riêng, mang lại cảm giác ấm cúng và gần gũi.',
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

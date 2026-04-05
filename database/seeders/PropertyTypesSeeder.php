<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

final class PropertyTypesSeeder extends Seeder
{
    public function run(): void
    {
        $propertyTypes = [
            [
                'name' => 'Khách sạn (Hotel)',
                'description' => 'Cơ sơ lưu trú từ 1-5 sao với đầy đủ dịch vụ buồng phòng, lễ tân và tiện ích đi kèm.',
            ],
            [
                'name' => 'Nhà nghỉ (Motel/Guesthouse)',
                'description' => 'Cơ sở lưu trú quy mô nhỏ, cung cấp dịch vụ nghỉ ngơi cơ bản với giá cả phải chăng.',
            ],
            [
                'name' => 'Căn hộ chung cư',
                'description' => 'Căn hộ trong tòa nhà chung cư hiện đại với đầy đủ tiện ích và an ninh, phù hợp cho lưu trú ngắn và dài hạn.',
            ],
            [
                'name' => 'Căn hộ dịch vụ',
                'description' => 'Căn hộ với các tiện ích giống khách sạn như dọn phòng, giặt là, thường dành cho chuyên gia hoặc khách lưu trú lâu.',
            ],
            [
                'name' => 'Condotel',
                'description' => 'Sự kết hợp giữa căn hộ và khách sạn, cho phép khách hàng sở hữu và cho thuê lại như một phòng khách sạn.',
            ],
            [
                'name' => 'Biệt thự (Villa)',
                'description' => 'Không gian lưu trú sang trọng, riêng biệt với sân vườn, hồ bơi và nhiều phòng ngủ cho gia đình hoặc nhóm bạn.',
            ],
            [
                'name' => 'Homestay',
                'description' => 'Trải nghiệm sống cùng người dân bản địa, tìm hiểu văn hoá và con người tại địa phương.',
            ],
            [
                'name' => 'Resort (Khu nghỉ dưỡng)',
                'description' => 'Tổ hợp nghỉ dưỡng cao cấp với cảnh quan đẹp, dịch vụ giải trí, spa và nhà hàng đẳng cấp.',
            ],
            [
                'name' => 'Bungalow',
                'description' => 'Nhà một tầng tách biệt, thiết kế đơn giản nhưng ấm cúng, thường nằm trong các khu nghỉ dưỡng ven biển hoặc núi.',
            ],
            [
                'name' => 'Tàu du lịch (Cruise)',
                'description' => 'Lưu trú trên tàu du lịch hạng sang, trải nghiệm ngủ đêm trên vịnh hoặc sông (Phổ biến tại Hạ Long, Lan Hạ).',
            ],
            [
                'name' => 'Phòng trọ / Nhà trọ',
                'description' => 'Loại hình cho thuê phổ biến cho sinh viên và người lao động, tập trung vào tính tiện dụng và giá rẻ.',
            ],
            [
                'name' => 'Camping / Glamping',
                'description' => 'Hình thức cắm trại ngoài trời, trong đó Glamping cung cấp đầy đủ tiện nghi cao cấp hơn truyền thống.',
            ],
            [
                'name' => 'Penthouse',
                'description' => 'Căn hộ nằm trên tầng cao nhất của một tòa nhà cao tầng, mang lại không gian sống đẳng cấp và tầm nhìn tuyệt đẹp.',
            ],
            [
                'name' => 'Studio',
                'description' => 'Căn hộ nhỏ gọn không vách ngăn giữa phòng ngủ, phòng khách và bếp, phù hợp cho người độc thân.',
            ],
            [
                'name' => 'Shophouse / Officetel',
                'description' => 'Mô hình kết hợp giữa nhà ở/văn phòng và kinh doanh, nằm ở các tầng đế của tòa nhà hoặc khu đô thị.',
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

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
                'name' => 'Căn hộ',
                'description' => 'Căn hộ chung cư cao tầng hiện đại với đầy đủ tiện ích và thang máy, phù hợp cho lưu trú dài hạn.',
            ],
            [
                'name' => 'Phòng Studio',
                'description' => 'Phòng Studio thiết kế mở với bếp nhỏ và không gian sống gọn gàng, lý tưởng cho người đi làm trẻ.',
            ],
            [
                'name' => 'Biệt thự',
                'description' => 'Biệt thự riêng biệt với sân vườn hoặc hồ bơi, được thiết kế cho kỳ nghỉ gia đình và trải nghiệm cao cấp.',
            ],
            [
                'name' => 'Homestay',
                'description' => 'Không gian sống bản địa với sự hỗ trợ của chủ nhà, mang lại trải nghiệm văn hóa địa phương.',
            ],
            [
                'name' => 'Phòng ở ghép',
                'description' => 'Phòng ngủ chia sẻ tiết kiệm trong một căn hộ lớn, thường được sinh viên hoặc khách du lịch bụi lựa chọn.',
            ],
            [
                'name' => 'Nhà phố',
                'description' => 'Nhà mặt đất nhiều tầng với lối đi riêng, thường nằm trong các khu dân cư đô thị.',
            ],
            [
                'name' => 'Penthouse',
                'description' => 'Căn hộ cao cấp trên tầng thượng với tầm nhìn toàn cảnh và các dịch vụ đặc quyền.',
            ],
            [
                'name' => 'Khu nghỉ dưỡng',
                'description' => 'Bất động sản nghỉ dưỡng đầy đủ dịch vụ với nhà hàng, spa và các khu vui chơi giải trí tại chỗ.',
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

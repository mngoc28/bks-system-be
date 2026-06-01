<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TouristSpotsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('tourist_spots')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $spots = [
            // Hà Nội
            ['name' => 'Hồ Hoàn Kiếm', 'slug' => Str::slug('Hồ Hoàn Kiếm'), 'category' => 'lake', 'region_label' => 'Hà Nội', 'is_featured' => true, 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Lăng Chủ tịch Hồ Chí Minh', 'slug' => Str::slug('Lăng Chủ tịch Hồ Chí Minh'), 'category' => 'landmark', 'region_label' => 'Hà Nội', 'is_featured' => true, 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Văn Miếu - Quốc Tử Giám', 'slug' => Str::slug('Văn Miếu - Quốc Tử Giám'), 'category' => 'landmark', 'region_label' => 'Hà Nội', 'is_featured' => false, 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Chùa Một Cột', 'slug' => Str::slug('Chùa Một Cột'), 'category' => 'landmark', 'region_label' => 'Hà Nội', 'is_featured' => false, 'sort_order' => 4, 'is_active' => true],

            // Đà Nẵng
            ['name' => 'Bà Nà Hill', 'slug' => Str::slug('Bà Nà Hill'), 'category' => 'landmark', 'region_label' => 'Đà Nẵng', 'is_featured' => true, 'sort_order' => 5, 'is_active' => true],
            ['name' => 'Bãi biển Mỹ Khê', 'slug' => Str::slug('Bãi biển Mỹ Khê'), 'category' => 'beach', 'region_label' => 'Đà Nẵng', 'is_featured' => true, 'sort_order' => 6, 'is_active' => true],
            ['name' => 'Chùa Linh Ứng', 'slug' => Str::slug('Chùa Linh Ứng'), 'category' => 'landmark', 'region_label' => 'Đà Nẵng', 'is_featured' => false, 'sort_order' => 61, 'is_active' => true],
            ['name' => 'Cầu Rồng', 'slug' => Str::slug('Cầu Rồng'), 'category' => 'bridge', 'region_label' => 'Đà Nẵng', 'is_featured' => false, 'sort_order' => 7, 'is_active' => true],
            ['name' => 'Bán đảo Sơn Trà', 'slug' => Str::slug('Bán đảo Sơn Trà'), 'category' => 'landmark', 'region_label' => 'Đà Nẵng', 'is_featured' => false, 'sort_order' => 8, 'is_active' => true],

            // Hồ Chí Minh
            ['name' => 'Chợ Bến Thành', 'slug' => Str::slug('Chợ Bến Thành'), 'category' => 'landmark', 'region_label' => 'Hồ Chí Minh', 'is_featured' => true, 'sort_order' => 9, 'is_active' => true],
            ['name' => 'Nhà thờ Đức Bà', 'slug' => Str::slug('Nhà thờ Đức Bà'), 'category' => 'landmark', 'region_label' => 'Hồ Chí Minh', 'is_featured' => false, 'sort_order' => 10, 'is_active' => true],
            ['name' => 'Dinh Độc Lập', 'slug' => Str::slug('Dinh Độc Lập'), 'category' => 'landmark', 'region_label' => 'Hồ Chí Minh', 'is_featured' => false, 'sort_order' => 11, 'is_active' => true],
            ['name' => 'Bưu điện Trung tâm Sài Gòn', 'slug' => Str::slug('Bưu điện Trung tâm Sài Gòn'), 'category' => 'landmark', 'region_label' => 'Hồ Chí Minh', 'is_featured' => false, 'sort_order' => 12, 'is_active' => true],

            // Lâm Đồng
            ['name' => 'Hồ Xuân Hương', 'slug' => Str::slug('Hồ Xuân Hương'), 'category' => 'lake', 'region_label' => 'Lâm Đồng', 'is_featured' => true, 'sort_order' => 13, 'is_active' => true],
            ['name' => 'Thung lũng Tình Yêu', 'slug' => Str::slug('Thung lũng Tình Yêu'), 'category' => 'landmark', 'region_label' => 'Lâm Đồng', 'is_featured' => false, 'sort_order' => 14, 'is_active' => true],
            ['name' => 'Đỉnh Langbiang', 'slug' => Str::slug('Đỉnh Langbiang'), 'category' => 'mountain', 'region_label' => 'Lâm Đồng', 'is_featured' => false, 'sort_order' => 15, 'is_active' => true],

            // Khánh Hòa
            ['name' => 'VinWonders Nha Trang', 'slug' => Str::slug('VinWonders Nha Trang'), 'category' => 'landmark', 'region_label' => 'Khánh Hòa', 'is_featured' => true, 'sort_order' => 16, 'is_active' => true],
            ['name' => 'Tháp Bà Ponagar', 'slug' => Str::slug('Tháp Bà Ponagar'), 'category' => 'landmark', 'region_label' => 'Khánh Hòa', 'is_featured' => false, 'sort_order' => 17, 'is_active' => true],
            ['name' => 'Hòn Chồng', 'slug' => Str::slug('Hòn Chồng'), 'category' => 'landmark', 'region_label' => 'Khánh Hòa', 'is_featured' => false, 'sort_order' => 18, 'is_active' => true],

            // Quảng Ninh
            ['name' => 'Vịnh Hạ Long', 'slug' => Str::slug('Vịnh Hạ Long'), 'category' => 'landmark', 'region_label' => 'Quảng Ninh', 'is_featured' => true, 'sort_order' => 19, 'is_active' => true],
            ['name' => 'Yên Tử', 'slug' => Str::slug('Yên Tử'), 'category' => 'landmark', 'region_label' => 'Quảng Ninh', 'is_featured' => false, 'sort_order' => 20, 'is_active' => true],
            ['name' => 'Đảo Tuần Châu', 'slug' => Str::slug('Đảo Tuần Châu'), 'category' => 'landmark', 'region_label' => 'Quảng Ninh', 'is_featured' => false, 'sort_order' => 21, 'is_active' => true],

            // Huế
            ['name' => 'Đại Nội Huế', 'slug' => Str::slug('Đại Nội Huế'), 'category' => 'landmark', 'region_label' => 'Huế', 'is_featured' => true, 'sort_order' => 22, 'is_active' => true],
            ['name' => 'Chùa Thiên Mụ', 'slug' => Str::slug('Chùa Thiên Mụ'), 'category' => 'landmark', 'region_label' => 'Huế', 'is_featured' => false, 'sort_order' => 23, 'is_active' => true],
            ['name' => 'Cầu Tràng Tiền', 'slug' => Str::slug('Cầu Tràng Tiền'), 'category' => 'bridge', 'region_label' => 'Huế', 'is_featured' => false, 'sort_order' => 24, 'is_active' => true],

            // Lao Cai / Sa Pa (homepage MVP)
            ['name' => 'Sa Pa', 'slug' => 'sa-pa', 'category' => 'mountain', 'region_label' => 'Lào Cai', 'is_featured' => true, 'sort_order' => 25, 'is_active' => true],
            ['name' => 'Đỉnh Fansipan', 'slug' => Str::slug('Đỉnh Fansipan'), 'category' => 'mountain', 'region_label' => 'Lào Cai', 'is_featured' => false, 'sort_order' => 26, 'is_active' => true],
            ['name' => 'Bản Cát Cát', 'slug' => Str::slug('Bản Cát Cát'), 'category' => 'landmark', 'region_label' => 'Lào Cai', 'is_featured' => false, 'sort_order' => 27, 'is_active' => true],
            ['name' => 'Núi Hàm Rồng', 'slug' => Str::slug('Núi Hàm Rồng'), 'category' => 'mountain', 'region_label' => 'Lào Cai', 'is_featured' => false, 'sort_order' => 28, 'is_active' => true],

            // Hai Phong — Cat Ba island (homepage MVP)
            ['name' => 'Cát Bà', 'slug' => 'cat-ba', 'category' => 'landmark', 'region_label' => 'Hải Phòng', 'is_featured' => true, 'sort_order' => 29, 'is_active' => true],

            // Quang Ngai — Ly Son (homepage MVP)
            ['name' => 'Lý Sơn', 'slug' => 'ly-son', 'category' => 'landmark', 'region_label' => 'Quảng Ngãi', 'is_featured' => true, 'sort_order' => 30, 'is_active' => true],

            // Ninh Bình
            ['name' => 'Tràng An', 'slug' => Str::slug('Tràng An'), 'category' => 'landmark', 'region_label' => 'Ninh Bình', 'is_featured' => true, 'sort_order' => 31, 'is_active' => true],
            ['name' => 'Chùa Bái Đính', 'slug' => Str::slug('Chùa Bái Đính'), 'category' => 'landmark', 'region_label' => 'Ninh Bình', 'is_featured' => false, 'sort_order' => 32, 'is_active' => true],
            ['name' => 'Hang Múa', 'slug' => Str::slug('Hang Múa'), 'category' => 'landmark', 'region_label' => 'Ninh Bình', 'is_featured' => false, 'sort_order' => 33, 'is_active' => true],
        ];

        $provincesByName = DB::table('provinces')->pluck('id', 'name');

        foreach ($spots as $s) {
            $regionLabel = $s['region_label'] ?? null;
            $provinceId = $regionLabel !== null ? $provincesByName->get($regionLabel) : null;

            DB::table('tourist_spots')->insert(array_merge($s, [
                'province_id' => $provinceId !== null ? (int) $provinceId : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}

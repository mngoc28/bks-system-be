<?php

declare (strict_types = 1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class AmenitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('amenities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $amenities = [
            'WiFi miễn phí',
            'Điều hòa',
            'Tủ lạnh',
            'TV',
            'Máy nước nóng',
            'Bếp',
            'Máy giặt',
            'Ban công',
            'Thang máy',
            'Bãi đỗ xe',
            'Bảo vệ 24/7',
            'Camera an ninh',
            'Hệ thống báo cháy',
            'Cửa sổ lớn',
            'Nội thất đầy đủ',
            'Tủ quần áo',
            'Bàn làm việc',
            'Ghế văn phòng',
            'Máy sấy tóc',
            'Đồ vệ sinh cá nhân',
            'Lò vi sóng',
            'Bếp từ',
            'Ấm đun nước',
            'Máy pha cà phê',
            'Bàn ăn',
            'Sofa',
            'Giường đôi',
            'Giường đơn',
            'Rèm cửa',
            'Đèn LED',
            'Quạt trần',
            'Hệ thống cách âm',
            'Sàn gỗ',
            'Phòng tắm riêng',
            'Bồn tắm',
            'Vòi sen',
            'Gương lớn',
            'Két sắt',
            'Minibar',
            'Smart TV',
        ];

        foreach ($amenities as $amenity) {
            DB::table('amenities')->insert([
                'name'       => $amenity,
                'created_by' => $faker->randomElement($adminPartnerIds),
                'updated_by' => $faker->randomElement($adminPartnerIds),
                'created_at' => Carbon::now()->subDays(rand(1, 40)),
                'updated_at' => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }
    }
}

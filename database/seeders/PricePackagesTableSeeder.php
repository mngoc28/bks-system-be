<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PricePackagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('price_packages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $packages = [
            [
                'name' => 'super small',
                'description' => 'Gói siêu nhỏ - Phù hợp cho 1 người, diện tích dưới 20m². Bao gồm các tiện ích cơ bản: giường đơn, tủ quần áo, bàn làm việc, phòng tắm riêng, WiFi, điều hòa. Giá cả phải chăng, lý tưởng cho sinh viên hoặc người đi công tác ngắn hạn.',
            ],
            [
                'name' => 'small',
                'description' => 'Gói nhỏ - Phù hợp cho 1-2 người, diện tích 20-30m². Bao gồm: giường đôi, tủ quần áo, bàn làm việc, bếp mini, phòng tắm riêng, WiFi, điều hòa, TV. Không gian thoải mái cho cặp đôi hoặc khách công tác.',
            ],
            [
                'name' => 'medium',
                'description' => 'Gói trung bình - Phù hợp cho 2-3 người, diện tích 30-45m². Bao gồm: phòng ngủ riêng, phòng khách, bếp đầy đủ, phòng tắm rộng, WiFi tốc độ cao, điều hòa, Smart TV, ban công. Phù hợp cho gia đình nhỏ hoặc khách lưu trú dài hạn.',
            ],
            [
                'name' => 'large',
                'description' => 'Gói lớn - Phù hợp cho 3-5 người, diện tích trên 45m². Bao gồm: 2 phòng ngủ, phòng khách rộng, bếp đầy đủ tiện nghi, 2 phòng tắm, WiFi tốc độ cao, điều hòa trung tâm, Smart TV 4K, ban công lớn, view đẹp. Phù hợp cho gia đình lớn hoặc khách VIP.',
            ],
        ];

        foreach ($packages as $package) {
            DB::table('price_packages')->insert([
                'name' => $package['name'],
                'description' => $package['description'],
                'created_by' => $faker->randomElement($adminPartnerIds),
                'updated_by' => $faker->randomElement($adminPartnerIds),
                'created_at' => Carbon::now()->subDays(rand(1, 40)),
                'updated_at' => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }
    }
}
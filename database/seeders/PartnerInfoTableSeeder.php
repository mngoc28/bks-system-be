<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PartnerInfoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('partner_info')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $partnerUserIds = DB::table('users')
            ->where('role', 'partner')
            ->pluck('id')
            ->toArray();

        if (empty($partnerUserIds)) {
            $partnerUserIds = range(2, 21); // Assuming first 20 users after admin are partners
        }

        $provinceIds = DB::table('provinces')->pluck('id')->toArray();
        $wardIds     = DB::table('wards')->pluck('id')->toArray();

        if (empty($provinceIds) || empty($wardIds)) {
            $this->command->warn('No provinces or wards found. Please run ProvincesTableSeeder and WardsTableSeeder first.');
            return;
        }

        $companyNames = [
            'Aman Resorts & Villas',
            'Sapa Jade Hill Homestay',
            'Eco Garden Lodge',
            'Hanoi Old Quarter Retreats',
            'Lagom Homestay & Studio',
            'Riverside Villa Group',
            'The Lanna Boutique Hotel',
            'Hidden Gem Homestay Đà Nẵng',
            'Bamboo House Hội An',
            'Tropic Escape Nha Trang',
            'Sunset Terrace Đà Lạt',
            'Lotus Pond Boutique Stay',
            'Heritage Corner Huế',
            'Mekong Breeze Cần Thơ',
            'Skyline Suite Hà Nội',
        ];

        $descriptions = [
            'Chúng tôi tin rằng mỗi chuyến đi đều đáng được một chỗ nghỉ ngơi thực sự. Với không gian thiết kế riêng biệt, dịch vụ tận tâm và vị trí đắc địa, chúng tôi cam kết mang lại cảm giác "nhà thứ hai" cho từng vị khách.',
            'Nestled among misty valleys and rice terraces, our homestay offers an authentic mountain experience. Wake up to birdsong, enjoy home-cooked Vietnamese breakfasts and end the day by the bonfire with local hosts who share their stories.',
            'Từng góc nhỏ của chúng tôi được thiết kế để mang lại cảm giác bình yên tuyệt đối. Vườn cây xanh mát, tiếng suối róc rách, và những bữa sáng tự nấu bằng nguyên liệu địa phương tươi nhất — đây là nơi bạn thực sự được nghỉ ngơi.',
            'Tọa lạc trong lòng phố cổ, chúng tôi mang đến không gian kết hợp hoàn hảo giữa nét cổ kính trăm năm và tiện nghi hiện đại. Mỗi phòng là một câu chuyện riêng về lịch sử và văn hóa Hà Nội.',
            'A curated collection of studios and apartments designed for the mindful traveler. Clean Scandinavian aesthetics meet Vietnamese warmth — perfect for long stays, work-from-anywhere escapes, or romantic getaways.',
            'Nestled beside a serene river, our villa collection is your private retreat from the world. Plunge pools, open-air pavilions, lush tropical gardens and a team dedicated to making your every wish come true.',
            'Kết hợp kiến trúc Thái truyền thống với những tiện nghi hiện đại bậc nhất, khách sạn boutique của chúng tôi tạo ra không gian trú ẩn thực sự sang trọng giữa thiên nhiên xanh mát. Hãy để chúng tôi chăm sóc bạn như thượng khách.',
            'Giấu mình sau những con phố nhỏ bình yên của Đà Nẵng, Hidden Gem là nơi dành cho những người tìm kiếm sự yên tĩnh và kết nối thực sự. Bể bơi tràn bờ, vườn nhiệt đới và những buổi chiều ngắm hoàng hôn bất tận.',
            'Stepping into Bamboo House is stepping into the Hội An of a century ago. Handcrafted lanterns, fragrant jasmine courtyards, and hosts who treat every guest like a long-lost family member.',
            'Kỳ nghỉ lý tưởng bên bờ biển Nha Trang bắt đầu từ đây. Thiết kế mở đón gió biển, hồ bơi vô cực nhìn ra đại dương, và dịch vụ nhà hàng hải sản tươi sống ngay tại chỗ cho bữa tối của bạn.',
            'Trên độ cao 1.500m giữa thành phố mộng mơ, Sunset Terrace mang đến những buổi chiều thưởng trà ngắm mây và đêm ngủ trong không khí trong lành mát mẻ. Thiên đường cho những ai yêu sự lãng mạn.',
            'A tranquil sanctuary surrounded by lotus ponds and swaying palms. Our boutique guesthouse blends traditional architecture with modern comfort, offering a peaceful escape for couples, solo travellers and families alike.',
            'Ở đây, bạn sẽ sống giữa những rêu phong và hoàng tử đình làng. Di sản kiến trúc Huế được bảo tồn tỉ mỉ trong từng viên gạch, kết hợp với những dịch vụ cao cấp tận tâm để mang lại một trải nghiệm lưu trú không thể quên.',
            'Trôi theo nhịp sông Mekong thong dong trên con thuyền nhỏ truyền thống, rồi về nhà nghỉ ngơi trong không gian mát mẻ bên sông. Chúng tôi mang cả hương vị miền Tây sông nước đến từng bữa ăn cho bạn.',
            'Từ tầng cao nhìn xuống Hồ Tây lấp lánh trong buổi bình minh — đây là trải nghiệm mà Skyline Suite mang lại cho từng vị khách. Phòng suite rộng rãi, dịch vụ concierge 24/7, và ẩm thực fine-dining ngay tại tầng 30.',
        ];

        // Verified Unsplash URLs (exterior / common area — not room interiors)
        $u = static fn (string $id): string => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w=800&q=80";

        $partnerBrandImages = [
            ['image_1' => $u('photo-1566073771259-6a8506099945'), 'image_2' => $u('photo-1520250497591-112f2f40a3f4'), 'image_3' => $u('photo-1512917774080-9991f1c4c750')],
            ['image_1' => $u('photo-1470071459604-3b5ec3a7fe05'), 'image_2' => $u('photo-1500530855697-b586d89ba3ee'), 'image_3' => $u('photo-1520250497591-112f2f40a3f4')],
            ['image_1' => $u('photo-1564013799919-ab600027ffc6'), 'image_2' => $u('photo-1580587771525-78b9dba3b914'), 'image_3' => $u('photo-1445019980597-93fa8acb246c')],
            ['image_1' => $u('photo-1551882547-ff40c63fe5fa'), 'image_2' => $u('photo-1545324418-cc1a3fa10c00'), 'image_3' => $u('photo-1600596542815-ffad4c1539a9')],
            ['image_1' => $u('photo-1522708323590-d24dbb6b0267'), 'image_2' => $u('photo-1600585154340-be6161a56a0c'), 'image_3' => $u('photo-1560185007-c5ca9d2c014d')],
            ['image_1' => $u('photo-1439066615861-d1af74d74000'), 'image_2' => $u('photo-1507525428034-b723cf961d3e'), 'image_3' => $u('photo-1496417263034-38ec4f0b665a')],
            ['image_1' => $u('photo-1582719478250-c89cae4dc85b'), 'image_2' => $u('photo-1566073771259-6a8506099945'), 'image_3' => $u('photo-1571003123894-1f0594d2b5d9')],
            ['image_1' => $u('photo-1571003123894-1f0594d2b5d9'), 'image_2' => $u('photo-1496417263034-38ec4f0b665a'), 'image_3' => $u('photo-1559592413-7cec4d0cae2b')],
            ['image_1' => $u('photo-1560185007-c5ca9d2c014d'), 'image_2' => $u('photo-1559592413-7cec4d0cae2b'), 'image_3' => $u('photo-1580587771525-78b9dba3b914')],
            ['image_1' => $u('photo-1496417263034-38ec4f0b665a'), 'image_2' => $u('photo-1507525428034-b723cf961d3e'), 'image_3' => $u('photo-1512917774080-9991f1c4c750')],
            ['image_1' => $u('photo-1500530855697-b586d89ba3ee'), 'image_2' => $u('photo-1470071459604-3b5ec3a7fe05'), 'image_3' => $u('photo-1520250497591-112f2f40a3f4')],
            ['image_1' => $u('photo-1445019980597-93fa8acb246c'), 'image_2' => $u('photo-1564013799919-ab600027ffc6'), 'image_3' => $u('photo-1571003123894-1f0594d2b5d9')],
            ['image_1' => $u('photo-1567272131881-8ce2275deb67'), 'image_2' => $u('photo-1560185007-c5ca9d2c014d'), 'image_3' => $u('photo-1551882547-ff40c63fe5fa')],
            ['image_1' => $u('photo-1507525428034-b723cf961d3e'), 'image_2' => $u('photo-1439066615861-d1af74d74000'), 'image_3' => $u('photo-1496417263034-38ec4f0b665a')],
            ['image_1' => $u('photo-1545324418-cc1a3fa10c00'), 'image_2' => $u('photo-1600596542815-ffad4c1539a9'), 'image_3' => $u('photo-1551882547-ff40c63fe5fa')],
        ];

        $majorProvinceIds = DB::table('provinces')
            ->whereIn('name', ['Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng'])
            ->pluck('id')
            ->toArray();

        $provinces = DB::table('provinces')->get(['id', 'name'])->keyBy('name');

        $companyProvinceMap = [
            'Aman Resorts & Villas'       => 'Hồ Chí Minh',
            'Sapa Jade Hill Homestay'     => 'Lào Cai',
            'Eco Garden Lodge'            => 'Hồ Chí Minh',
            'Hanoi Old Quarter Retreats'  => 'Hà Nội',
            'Lagom Homestay & Studio'     => 'Hồ Chí Minh',
            'Riverside Villa Group'       => 'Hồ Chí Minh',
            'The Lanna Boutique Hotel'    => 'Hồ Chí Minh',
            'Hidden Gem Homestay Đà Nẵng' => 'Đà Nẵng',
            'Bamboo House Hội An'         => 'Đà Nẵng',
            'Tropic Escape Nha Trang'     => 'Khánh Hòa',
            'Sunset Terrace Đà Lạt'       => 'Lâm Đồng',
            'Lotus Pond Boutique Stay'    => 'Hồ Chí Minh',
            'Heritage Corner Huế'         => 'Huế',
            'Mekong Breeze Cần Thơ'       => 'Cần Thơ',
            'Skyline Suite Hà Nội'        => 'Hà Nội',
        ];

        $wardsByProvince = [];

        foreach ($partnerUserIds as $index => $userId) {
            $companyName = $companyNames[$index % count($companyNames)];
            $provinceName = $companyProvinceMap[$companyName] ?? 'Hồ Chí Minh';

            if (isset($provinces[$provinceName])) {
                $provinceId = $provinces[$provinceName]->id;
            } else {
                $provinceId = $faker->randomElement($provinceIds);
            }

            if (!isset($wardsByProvince[$provinceId])) {
                $wardsByProvince[$provinceId] = DB::table('wards')
                    ->where('province_id', $provinceId)
                    ->pluck('id')
                    ->toArray();
            }

            $provinceWards = $wardsByProvince[$provinceId];
            $wardId = !empty($provinceWards)
                ? $faker->randomElement($provinceWards)
                : $faker->randomElement($wardIds);

            DB::table('partner_info')->insert([
                'user_id'      => $userId,
                'province_id'  => $provinceId,
                'ward_id'      => $wardId,
                'address'      => $faker->streetAddress(),
                'company_name' => $companyName,
                'phone'        => $faker->phoneNumber(),
                'website'      => $faker->optional(0.9)->url(),
                'description'  => $descriptions[$index % count($descriptions)],
                'image_1'      => $partnerBrandImages[$index % count($partnerBrandImages)]['image_1'],
                'image_2'      => $partnerBrandImages[$index % count($partnerBrandImages)]['image_2'],
                'image_3'      => $partnerBrandImages[$index % count($partnerBrandImages)]['image_3'],
                'created_by'   => $faker->randomElement($adminPartnerIds),
                'updated_by'   => $faker->randomElement($adminPartnerIds),
                'created_at'   => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'   => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }
    }
}

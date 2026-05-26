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
            'Công ty TNHH Bất động sản ABC',
            'Công ty CP Đầu tư và Phát triển XYZ',
            'Công ty TNHH Dịch vụ Nhà ở DEF',
            'Công ty CP Quản lý Tòa nhà GHI',
            'Công ty TNHH Bất động sản JKL',
            'Công ty CP Đầu tư Bất động sản MNO',
            'Công ty TNHH Quản lý Chung cư PQR',
            'Công ty CP Phát triển Đô thị STU',
            'Công ty TNHH Dịch vụ Bất động sản VWX',
            'Công ty CP Đầu tư Nhà ở YZ',
            'Công ty TNHH Quản lý Tài sản Alpha',
            'Công ty CP Bất động sản Beta',
            'Công ty TNHH Đầu tư Gamma',
            'Công ty CP Phát triển Delta',
            'Công ty TNHH Dịch vụ Epsilon',
        ];

        $descriptions = [
            'Chuyên cung cấp các dịch vụ bất động sản chất lượng cao, với đội ngũ nhân viên chuyên nghiệp và giàu kinh nghiệm. Cam kết mang đến những giải pháp tốt nhất cho khách hàng.',
            'Công ty hàng đầu trong lĩnh vực đầu tư và phát triển bất động sản, với nhiều dự án thành công trên khắp cả nước. Luôn đặt uy tín và chất lượng lên hàng đầu.',
            'Chuyên về dịch vụ quản lý nhà ở và chung cư, đảm bảo không gian sống an toàn, tiện nghi cho cư dân. Dịch vụ chăm sóc khách hàng 24/7.',
            'Với hơn 10 năm kinh nghiệm trong ngành, chúng tôi tự hào là đối tác tin cậy của nhiều khách hàng. Chuyên về quản lý và vận hành tòa nhà cao cấp.',
            'Công ty chuyên đầu tư và phát triển các dự án bất động sản quy mô lớn, tạo ra những không gian sống hiện đại và đẳng cấp cho cộng đồng.',
            'Đội ngũ chuyên nghiệp với nhiều năm kinh nghiệm trong lĩnh vực bất động sản, cam kết mang đến dịch vụ tốt nhất và giá trị cao nhất cho khách hàng.',
            'Chuyên về quản lý và vận hành các tòa nhà chung cư, đảm bảo môi trường sống an toàn, văn minh cho cư dân. Dịch vụ hỗ trợ tận tâm và chuyên nghiệp.',
            'Công ty phát triển đô thị hàng đầu, chuyên về các dự án nhà ở và hạ tầng đô thị. Tạo ra những không gian sống hiện đại, tiện nghi cho cộng đồng.',
            'Với phương châm "Khách hàng là trung tâm", chúng tôi luôn nỗ lực mang đến những dịch vụ bất động sản chất lượng cao, đáp ứng mọi nhu cầu của khách hàng.',
            'Chuyên đầu tư và phát triển các dự án nhà ở với tiêu chuẩn quốc tế, tạo ra những không gian sống lý tưởng cho gia đình Việt Nam.',
            'Công ty quản lý tài sản chuyên nghiệp, giúp khách hàng tối ưu hóa giá trị đầu tư bất động sản. Dịch vụ tư vấn và hỗ trợ toàn diện.',
            'Với đội ngũ nhân viên giàu kinh nghiệm và nhiệt huyết, chúng tôi cam kết mang đến những giải pháp bất động sản tốt nhất, phù hợp với từng nhu cầu cụ thể.',
            'Chuyên về đầu tư và phát triển các dự án bất động sản thương mại và nhà ở, tạo ra giá trị bền vững cho khách hàng và cộng đồng.',
            'Công ty phát triển bất động sản với tầm nhìn dài hạn, tập trung vào các dự án chất lượng cao, đảm bảo lợi ích lâu dài cho khách hàng và nhà đầu tư.',
            'Chuyên cung cấp các dịch vụ bất động sản toàn diện, từ tư vấn, mua bán, cho thuê đến quản lý tài sản. Đội ngũ chuyên nghiệp, dịch vụ tận tâm.',
        ];

        $majorProvinceIds = DB::table('provinces')
            ->whereIn('name', ['Hà Nội', 'Hồ Chí Minh', 'Đà Nẵng'])
            ->pluck('id')
            ->toArray();

        foreach ($partnerUserIds as $userId) {
            // 75% chance of placing the partner in a major city (Hanoi, HCMC, Da Nang)
            if (rand(1, 100) <= 75 && !empty($majorProvinceIds)) {
                $provinceId = $faker->randomElement($majorProvinceIds);
            } else {
                $provinceId = $faker->randomElement($provinceIds);
            }

            $provinceWards = DB::table('wards')
                ->where('province_id', $provinceId)
                ->pluck('id')
                ->toArray();

            $wardId = ! empty($provinceWards)
                ? $faker->randomElement($provinceWards)
                : $faker->randomElement($wardIds);

            DB::table('partner_info')->insert([
                'user_id'      => $userId,
                'province_id'  => $provinceId,
                'ward_id'      => $wardId,
                'address'      => $faker->streetAddress(),
                'company_name' => $faker->randomElement($companyNames),
                'phone'        => $faker->phoneNumber(),
                'website'      => $faker->optional(0.9)->url(),
                'description'  => $faker->randomElement($descriptions),
                'image_1'      => null,
                'image_2'      => null,
                'image_3'      => null,
                'created_by'   => $faker->randomElement($adminPartnerIds),
                'updated_by'   => $faker->randomElement($adminPartnerIds),
                'created_at'   => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'   => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }
    }
}

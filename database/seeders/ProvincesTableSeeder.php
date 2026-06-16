<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class ProvincesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('provinces')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Get admin user ID for created_by/updated_by
        $adminId = DB::table('users')
            ->where('role', 'admin')
            ->value('id');

        if (! $adminId) {
            $adminId = 1; // Default to ID 1 if admin doesn't exist yet
        }

$provinces = [
            ['id' => 1, 'name' => 'Hà Nội', 'name_en' => 'ha_noi', 'image' => '/v1781324044/provinces/tprpzlxsu9pndlm9iqzq.webp'],
            ['id' => 2, 'name' => 'Cao Bằng', 'name_en' => 'cao_bang', 'image' => '/v1781323842/provinces/noenno3genslzulmjeil.webp'],
            ['id' => 3, 'name' => 'Tuyên Quang', 'name_en' => 'tuyen_quang', 'image' => '/v1781323884/provinces/f08tzmckyggpszhgapco.webp'],
            ['id' => 4, 'name' => 'Điện Biên', 'name_en' => 'dien_bien', 'image' => '/v1781323593/provinces/bynectqikh9ywpn40gcv.webp'],
            ['id' => 5, 'name' => 'Lai Châu', 'name_en' => 'lai_chau', 'image' => '/v1781323805/provinces/i7vb1lwk6e7oowx5yufy.webp'],
            ['id' => 6, 'name' => 'Sơn La', 'name_en' => 'son_la', 'image' => '/v1781323515/provinces/gn7qydchmi1kah6yfjxm.webp'],
            ['id' => 7, 'name' => 'Lào Cai', 'name_en' => 'lao_cai', 'image' => '/v1781323719/provinces/npynpdpc3tbeczvwlsnk.webp'],
            ['id' => 8, 'name' => 'Thái Nguyên', 'name_en' => 'thai_nguyen', 'image' => '/v1781323680/provinces/qfk82zy3nbaxsoza1g48.webp'],
            ['id' => 9, 'name' => 'Lạng Sơn', 'name_en' => 'lang_son', 'image' => '/v1781323556/provinces/l7i7tybkalz72tbyqfv3.webp'],
            ['id' => 10, 'name' => 'Quảng Ninh', 'name_en' => 'quang_ninh', 'image' => '/v1781323309/provinces/jsi7q6brixees25gv9x8.webp'],
            ['id' => 11, 'name' => 'Bắc Ninh', 'name_en' => 'bac_ninh', 'image' => '/v1781323361/provinces/qh84ppd9yaoxcnqobojg.webp'],
            ['id' => 12, 'name' => 'Phú Thọ', 'name_en' => 'phu_tho', 'image' => '/v1781323450/provinces/yofy4p5zr7abriyjr32v.webp'],
            ['id' => 13, 'name' => 'Hải Phòng', 'name_en' => 'hai_phong', 'image' => '/v1781321673/provinces/qkib4ftfkupazzb7mx0f.webp'],
            ['id' => 14, 'name' => 'Hưng Yên', 'name_en' => 'hung_yen', 'image' => '/v1781323262/provinces/ctgyojfkca5fotuczt4b.webp'],
            ['id' => 15, 'name' => 'Ninh Bình', 'name_en' => 'ninh_binh', 'image' => '/v1781323169/provinces/e2pxfzo2bo6hmudeawtk.webp'],
            ['id' => 16, 'name' => 'Thanh Hóa', 'name_en' => 'thanh_hoa', 'image' => '/v1781323139/provinces/uxvp7emf9y9pn3bheggk.webp'],
            ['id' => 17, 'name' => 'Nghệ An', 'name_en' => 'nghe_an', 'image' => '/v1781323097/provinces/zdvywti72lwz5xgjxrif.webp'],
            ['id' => 18, 'name' => 'Hà Tĩnh', 'name_en' => 'ha_tinh', 'image' => '/v1781323064/provinces/c63uygotpdt0bhsslost.webp'],
            ['id' => 19, 'name' => 'Quảng Trị', 'name_en' => 'quang_tri', 'image' => '/v1781323000/provinces/zw5uux0i7urefuv0cbbo.webp'],
            ['id' => 20, 'name' => 'Huế', 'name_en' => 'hue', 'image' => '/v1781323981/provinces/vxseovsxkk8y0spfxeub.webp'],
            ['id' => 21, 'name' => 'Đà Nẵng', 'name_en' => 'da_nang', 'image' => '/v1781320830/provinces/xzgxghixeuscnvcwujvv.webp'],
            ['id' => 22, 'name' => 'Quảng Ngãi', 'name_en' => 'quang_ngai', 'image' => '/v1781322945/provinces/fngol6lacpmkocwwrl10.webp'],
            ['id' => 23, 'name' => 'Gia Lai', 'name_en' => 'gia_lai', 'image' => '/v1781322885/provinces/qbqr0usetnwkbf1zxfuc.webp'],
            ['id' => 24, 'name' => 'Khánh Hòa', 'name_en' => 'khanh_hoa', 'image' => '/v1781322757/provinces/ktheje3xlj5zuf2t1obi.webp'],
            ['id' => 25, 'name' => 'Đắk Lắk', 'name_en' => 'dak_lak', 'image' => '/v1781322849/provinces/bhuwh3kg4gjlnjvhhgjh.webp'],
            ['id' => 26, 'name' => 'Lâm Đồng', 'name_en' => 'lam_dong', 'image' => '/v1781322723/provinces/mfo7pimqlchpyk3lygoi.webp'],
            ['id' => 27, 'name' => 'Đồng Nai', 'name_en' => 'dong_nai', 'image' => '/v1781322609/provinces/zkbd6nkhpg0tw3nmlf4s.webp'],
            ['id' => 28, 'name' => 'Hồ Chí Minh', 'name_en' => 'ho_chi_minh', 'image' => '/v1781320909/provinces/oi53yoyhodsdjbco20tf.webp'],
            ['id' => 29, 'name' => 'Tây Ninh', 'name_en' => 'tay_ninh', 'image' => '/v1781322488/provinces/t7twmfvvm0xplovrk5vl.webp'],
            ['id' => 30, 'name' => 'Đồng Tháp', 'name_en' => 'dong_thap', 'image' => '/v1781322452/provinces/lbyknj3a5fxxxysaa23r.webp'],
            ['id' => 31, 'name' => 'Vĩnh Long', 'name_en' => 'vinh_long', 'image' => '/v1781322378/provinces/ilop52icgioe3je4drkm.webp'],
            ['id' => 32, 'name' => 'An Giang', 'name_en' => 'an_giang', 'image' => '/v1781322192/provinces/hwbztrx8kqdmcx3ollvb.webp'],
            ['id' => 33, 'name' => 'Cần Thơ', 'name_en' => 'can_tho', 'image' => '/v1781323950/provinces/kcy6xtahrtncubedrw6z.webp'],
            ['id' => 34, 'name' => 'Cà Mau', 'name_en' => 'ca_mau', 'image' => '/v1781321754/provinces/ngf0kurj3vvzcyex5vsn.webp'],
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
                'id'         => $province['id'],
                'name'       => $province['name'],
                'name_en'    => $province['name_en'],
                'image'      => $province['image'] ?? null,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

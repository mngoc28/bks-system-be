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
            ['id' => 1, 'name' => 'Hà Nội', 'name_en' => 'ha_noi'],
            ['id' => 2, 'name' => 'Cao Bằng', 'name_en' => 'cao_bang'],
            ['id' => 3, 'name' => 'Tuyên Quang', 'name_en' => 'tuyen_quang'],
            ['id' => 4, 'name' => 'Điện Biên', 'name_en' => 'dien_bien'],
            ['id' => 5, 'name' => 'Lai Châu', 'name_en' => 'lai_chau'],
            ['id' => 6, 'name' => 'Sơn La', 'name_en' => 'son_la'],
            ['id' => 7, 'name' => 'Lào Cai', 'name_en' => 'lao_cai'],
            ['id' => 8, 'name' => 'Thái Nguyên', 'name_en' => 'thai_nguyen'],
            ['id' => 9, 'name' => 'Lạng Sơn', 'name_en' => 'lang_son'],
            ['id' => 10, 'name' => 'Quảng Ninh', 'name_en' => 'quang_ninh'],
            ['id' => 11, 'name' => 'Bắc Ninh', 'name_en' => 'bac_ninh'],
            ['id' => 12, 'name' => 'Phú Thọ', 'name_en' => 'phu_tho'],
            ['id' => 13, 'name' => 'Hải Phòng', 'name_en' => 'hai_phong'],
            ['id' => 14, 'name' => 'Hưng Yên', 'name_en' => 'hung_yen'],
            ['id' => 15, 'name' => 'Ninh Bình', 'name_en' => 'ninh_binh'],
            ['id' => 16, 'name' => 'Thanh Hóa', 'name_en' => 'thanh_hoa'],
            ['id' => 17, 'name' => 'Nghệ An', 'name_en' => 'nghe_an'],
            ['id' => 18, 'name' => 'Hà Tĩnh', 'name_en' => 'ha_tinh'],
            ['id' => 19, 'name' => 'Quảng Trị', 'name_en' => 'quang_tri'],
            ['id' => 20, 'name' => 'Huế', 'name_en' => 'hue'],
            ['id' => 21, 'name' => 'Đà Nẵng', 'name_en' => 'da_nang'],
            ['id' => 22, 'name' => 'Quảng Ngãi', 'name_en' => 'quang_ngai'],
            ['id' => 23, 'name' => 'Gia Lai', 'name_en' => 'gia_lai'],
            ['id' => 24, 'name' => 'Khánh Hòa', 'name_en' => 'khanh_hoa'],
            ['id' => 25, 'name' => 'Đắk Lắk', 'name_en' => 'dak_lak'],
            ['id' => 26, 'name' => 'Lâm Đồng', 'name_en' => 'lam_dong'],
            ['id' => 27, 'name' => 'Đồng Nai', 'name_en' => 'dong_nai'],
            ['id' => 28, 'name' => 'Hồ Chí Minh', 'name_en' => 'ho_chi_minh'],
            ['id' => 29, 'name' => 'Tây Ninh', 'name_en' => 'tay_ninh'],
            ['id' => 30, 'name' => 'Đồng Tháp', 'name_en' => 'dong_thap'],
            ['id' => 31, 'name' => 'Vĩnh Long', 'name_en' => 'vinh_long'],
            ['id' => 32, 'name' => 'An Giang', 'name_en' => 'an_giang'],
            ['id' => 33, 'name' => 'Cần Thơ', 'name_en' => 'can_tho'],
            ['id' => 34, 'name' => 'Cà Mau', 'name_en' => 'ca_mau'],
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
                'id'         => $province['id'],
                'name'       => $province['name'],
                'name_en'    => $province['name_en'],
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

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
    ['name' => 'Hà Nội', 'name_en' => 'ha_noi'],
    ['name' => 'Cao Bằng', 'name_en' => 'cao_bang'],
    ['name' => 'Tuyên Quang', 'name_en' => 'tuyen_quang'],
    ['name' => 'Điện Biên', 'name_en' => 'dien_bien'],
    ['name' => 'Lai Châu', 'name_en' => 'lai_chau'],
    ['name' => 'Sơn La', 'name_en' => 'son_la'],
    ['name' => 'Lào Cai', 'name_en' => 'lao_cai'],
    ['name' => 'Thái Nguyên', 'name_en' => 'thai_nguyen'],
    ['name' => 'Lạng Sơn', 'name_en' => 'lang_son'],
    ['name' => 'Quảng Ninh', 'name_en' => 'quang_ninh'],
    ['name' => 'Bắc Ninh', 'name_en' => 'bac_ninh'],
    ['name' => 'Phú Thọ', 'name_en' => 'phu_tho'],
    ['name' => 'Hải Phòng', 'name_en' => 'hai_phong'],
    ['name' => 'Hưng Yên', 'name_en' => 'hung_yen'],
    ['name' => 'Ninh Bình', 'name_en' => 'ninh_binh'],
    ['name' => 'Thanh Hóa', 'name_en' => 'thanh_hoa'],
    ['name' => 'Nghệ An', 'name_en' => 'nghe_an'],
    ['name' => 'Hà Tĩnh', 'name_en' => 'ha_tinh'],
    ['name' => 'Quảng Trị', 'name_en' => 'quang_tri'],
    ['name' => 'Huế', 'name_en' => 'hue'],
    ['name' => 'Đà Nẵng', 'name_en' => 'da_nang'],
    ['name' => 'Quảng Ngãi', 'name_en' => 'quang_ngai'],
    ['name' => 'Gia Lai', 'name_en' => 'gia_lai'],
    ['name' => 'Khánh Hòa', 'name_en' => 'khanh_hoa'],
    ['name' => 'Đắk Lắk', 'name_en' => 'dak_lak'],
    ['name' => 'Lâm Đồng', 'name_en' => 'lam_dong'],
    ['name' => 'Đồng Nai', 'name_en' => 'dong_nai'],
    ['name' => 'Hồ Chí Minh', 'name_en' => 'ho_chi_minh'],
    ['name' => 'Tây Ninh', 'name_en' => 'tay_ninh'],
    ['name' => 'Đồng Tháp', 'name_en' => 'dong_thap'],
    ['name' => 'Vĩnh Long', 'name_en' => 'vinh_long'],
    ['name' => 'An Giang', 'name_en' => 'an_giang'],
    ['name' => 'Cần Thơ', 'name_en' => 'can_tho'],
    ['name' => 'Cà Mau', 'name_en' => 'ca_mau'],
];


        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
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

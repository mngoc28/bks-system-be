<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertiesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('properties')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $propertyNames = [
            'Chung cư Golden Palace',
            'Tòa nhà Diamond Tower',
            'Chung cư Sunrise City',
            'Tòa nhà Landmark 81',
            'Chung cư Vinhomes Central Park',
            'Tòa nhà Bitexco Financial Tower',
            'Chung cư Masteri Centre Point',
            'Tòa nhà Saigon Trade Center',
            'Chung cư The Manor',
            'Tòa nhà Vincom Center',
            'Chung cư Millennium Tower',
            'Tòa nhà Keangnam Hanoi Landmark',
            'Chung cư Ciputra Hanoi',
            'Tòa nhà Lotte Center Hanoi',
            'Chung cư The Vista',
            'Tòa nhà FLC Complex',
            'Chung cư Vinhomes Ocean Park',
            'Tòa nhà FPT Tower',
            'Chung cư The Nassim',
            'Tòa nhà Capital Place',
            'Chung cư The Garden',
            'Tòa nhà Indochina Plaza',
            'Chung cư The Nassim Saigon',
            'Tòa nhà Times Square',
            'Chung cư The Estella',
            'Tòa nhà The Sun Avenue',
            'Chung cư The Grand Manhattan',
            'Tòa nhà The EverRich II',
            'Chung cư The Nassim Hanoi',
            'Tòa nhà The Sun Tower',
            'Chung cư The Park Home',
            'Tòa nhà The Luxe',
            'Chung cư The Nassim Ho Chi Minh',
            'Tòa nhà The Landmark Plus',
            'Chung cư The Sun City',
            'Tòa nhà The Sun Grand City',
            'Chung cư The Sun Grand Residence',
            'Tòa nhà The Sun Grand View',
            'Chung cư The Sun Grand Palace',
            'Tòa nhà The Sun Grand Tower',
            'Chung cư The Sun Grand Estate',
            'Tòa nhà The Sun Grand Plaza',
            'Chung cư The Sun Grand Center',
            'Tòa nhà The Sun Grand Complex',
            'Chung cư The Sun Grand Residence 2',
            'Tòa nhà The Sun Grand View 2',
            'Chung cư The Sun Grand Palace 2',
            'Tòa nhà The Sun Grand Tower 2',
            'Chung cư The Sun Grand Estate 2',
        ];

        $addressData = [
            ['province' => 'Hà Nội', 'area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 1 Nguyễn Huy Tưởng, Phường Thanh Xuân Trung, Quận Thanh Xuân, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 45 Lê Văn Lương, Phường Nhân Chính, Quận Thanh Xuân, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 75 Hoàng Đạo Thúy, Phường Nhân Chính, Quận Thanh Xuân, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 90 Nguyễn Xiển, Phường Hạ Đình, Quận Thanh Xuân, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 125 Khuất Duy Tiến, Phường Nhân Chính, Quận Thanh Xuân, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 200 Lê Trọng Tấn, Phường Khương Mai, Quận Thanh Xuân, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 30 Phạm Văn Đồng, Phường Mai Dịch, Quận Cầu Giấy, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 60 Trần Duy Hưng, Phường Trung Hòa, Quận Cầu Giấy, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 105 Hoàng Quốc Việt, Phường Nghĩa Đô, Quận Cầu Giấy, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 135 Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 190 Dịch Vọng, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 250 Xuân Thủy, Phường Dịch Vọng Hậu, Quận Cầu Giấy, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 15 Nguyễn Chí Thanh, Phường Láng Thượng, Quận Đống Đa, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 120 Đường Láng, Phường Láng Thượng, Quận Đống Đa, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 180 Láng Hạ, Phường Láng Hạ, Quận Đống Đa, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 220 Tây Sơn, Phường Trung Liệt, Quận Đống Đa, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 280 Khâm Thiên, Phường Khâm Thiên, Quận Đống Đa, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Tây Hồ, Hà Nội', 'address' => 'Số 25 Võ Chí Công, Phường Xuân La, Quận Tây Hồ, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Tây Hồ, Hà Nội', 'address' => 'Số 68 Lạc Long Quân, Phường Bưởi, Quận Tây Hồ, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Tây Hồ, Hà Nội', 'address' => 'Số 120 Âu Cơ, Phường Nhật Tân, Quận Tây Hồ, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 150 Phố Huế, Phường Ngô Thì Nhậm, Quận Hai Bà Trưng, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 165 Bà Triệu, Phường Lê Đại Hành, Quận Hai Bà Trưng, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 210 Giải Phóng, Phường Đồng Tâm, Quận Hai Bà Trưng, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 320 Minh Khai, Phường Minh Khai, Quận Hai Bà Trưng, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Ba Đình, Hà Nội', 'address' => 'Số 195 Kim Mã, Phường Kim Mã, Quận Ba Đình, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Ba Đình, Hà Nội', 'address' => 'Số 45 Nguyễn Đình Thi, Phường Giảng Võ, Quận Ba Đình, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Ba Đình, Hà Nội', 'address' => 'Số 88 Đội Cấn, Phường Đội Cấn, Quận Ba Đình, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Hà Đông, Hà Nội', 'address' => 'Số 225 Trần Phú, Phường Mộ Lao, Quận Hà Đông, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Hà Đông, Hà Nội', 'address' => 'Số 240 Lê Trọng Tấn, Phường Dương Nội, Quận Hà Đông, Hà Nội'],
            ['province' => 'Hà Nội', 'area' => 'Quận Hà Đông, Hà Nội', 'address' => 'Số 350 Quang Trung, Phường Quang Trung, Quận Hà Đông, Hà Nội'],

            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 1 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 15 Đồng Khởi, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 30 Lê Lợi, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 45 Nguyễn Du, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 60 Pasteur, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 75 Nguyễn Trung Trực, Phường Bến Thành, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 135 Lý Tự Trọng, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 210 Trần Hưng Đạo, Phường Cô Giang, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 270 Võ Văn Kiệt, Phường Cầu Kho, Quận 1, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 90 Võ Văn Tần, Phường 6, Quận 3, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 105 Nguyễn Thị Minh Khai, Phường 6, Quận 3, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 120 Nguyễn Đình Chiểu, Phường 6, Quận 3, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 180 Võ Thị Sáu, Phường 8, Quận 3, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 225 Cách Mạng Tháng 8, Phường 10, Quận 3, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận Bình Thạnh, TP.Hồ Chí Minh', 'address' => 'Số 150 Điện Biên Phủ, Phường 25, Quận Bình Thạnh, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận Bình Thạnh, TP.Hồ Chí Minh', 'address' => 'Số 280 Xô Viết Nghệ Tĩnh, Phường 21, Quận Bình Thạnh, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận Bình Thạnh, TP.Hồ Chí Minh', 'address' => 'Số 350 Nguyễn Xí, Phường 13, Quận Bình Thạnh, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 7, TP.Hồ Chí Minh', 'address' => 'Số 255 Nguyễn Văn Linh, Phường Tân Thuận Đông, Quận 7, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 7, TP.Hồ Chí Minh', 'address' => 'Số 88 Nguyễn Thị Thập, Phường Tân Phú, Quận 7, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 7, TP.Hồ Chí Minh', 'address' => 'Số 120 Huỳnh Tấn Phát, Phường Tân Thuận Tây, Quận 7, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 2, TP.Hồ Chí Minh', 'address' => 'Số 68 Nguyễn Duy Trinh, Phường Bình Trưng Đông, Quận 2, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 2, TP.Hồ Chí Minh', 'address' => 'Số 120 Nguyễn Thị Định, Phường An Phú, Quận 2, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 2, TP.Hồ Chí Minh', 'address' => 'Số 200 Mai Chí Thọ, Phường Bình An, Quận 2, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 9, TP.Hồ Chí Minh', 'address' => 'Số 240 Lê Văn Việt, Phường Hiệp Phú, Quận 9, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 9, TP.Hồ Chí Minh', 'address' => 'Số 155 Đỗ Xuân Hợp, Phường Phước Long A, Quận 9, TP.Hồ Chí Minh'],
            ['province' => 'Hồ Chí Minh', 'area' => 'Quận 9, TP.Hồ Chí Minh', 'address' => 'Số 320 Nguyễn Duy Trinh, Phường Tăng Nhơn Phú A, Quận 9, TP.Hồ Chí Minh'],

            ['province' => 'Đà Nẵng', 'area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 1 Trần Phú, Phường Thạch Thang, Quận Hải Châu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 15 Lê Duẩn, Phường Thạch Thang, Quận Hải Châu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 45 Hoàng Diệu, Phường Nam Dương, Quận Hải Châu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 75 Nguyễn Hữu Thọ, Phường Hòa Thuận Nam, Quận Hải Châu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 90 Đống Đa, Phường Thạch Thang, Quận Hải Châu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 105 Lý Tự Trọng, Phường Hải Châu I, Quận Hải Châu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 135 Lê Đình Lý, Phường Hòa Cường Nam, Quận Hải Châu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Thanh Khê, Đà Nẵng', 'address' => 'Số 30 Nguyễn Văn Linh, Phường Vĩnh Trung, Quận Thanh Khê, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Thanh Khê, Đà Nẵng', 'address' => 'Số 60 Điện Biên Phủ, Phường Thanh Khê Tây, Quận Thanh Khê, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Thanh Khê, Đà Nẵng', 'address' => 'Số 155 Tôn Đức Thắng, Phường Thanh Khê Đông, Quận Thanh Khê, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Ngũ Hành Sơn, Đà Nẵng', 'address' => 'Số 120 Nguyễn Chí Thanh, Phường Mỹ An, Quận Ngũ Hành Sơn, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Ngũ Hành Sơn, Đà Nẵng', 'address' => 'Số 200 Hoàng Sa, Phường Mỹ An, Quận Ngũ Hành Sơn, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Sơn Trà, Đà Nẵng', 'address' => 'Số 88 Võ Nguyên Giáp, Phường Mỹ An, Quận Sơn Trà, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Sơn Trà, Đà Nẵng', 'address' => 'Số 150 Phạm Văn Đồng, Phường Phước Mỹ, Quận Sơn Trà, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Liên Chiểu, Đà Nẵng', 'address' => 'Số 250 Nguyễn Lương Bằng, Phường Hòa Hiệp Bắc, Quận Liên Chiểu, Đà Nẵng'],
            ['province' => 'Đà Nẵng', 'area' => 'Quận Liên Chiểu, Đà Nẵng', 'address' => 'Số 320 Tôn Đức Thắng, Phường Hòa Minh, Quận Liên Chiểu, Đà Nẵng'],

            ['province' => 'Quảng Ninh', 'area' => 'Thành phố Hạ Long, Quảng Ninh', 'address' => 'Số 88 Hạ Long, Phường Bãi Cháy, Thành phố Hạ Long, Quảng Ninh'],
            ['province' => 'Quảng Ninh', 'area' => 'Thành phố Hạ Long, Quảng Ninh', 'address' => 'Số 120 Hoàng Quốc Việt, Phường Hùng Thắng, Thành phố Hạ Long, Quảng Ninh'],

            ['province' => 'Lâm Đồng', 'area' => 'Thành phố Đà Lạt, Lâm Đồng', 'address' => 'Số 10 Trần Hưng Đạo, Phường 10, Thành phố Đà Lạt, Lâm Đồng'],
            ['province' => 'Lâm Đồng', 'area' => 'Thành phố Đà Lạt, Lâm Đồng', 'address' => 'Số 33 Phan Đình Phùng, Phường 2, Thành phố Đà Lạt, Lâm Đồng'],

            ['province' => 'Khánh Hòa', 'area' => 'Thành phố Nha Trang, Khánh Hòa', 'address' => 'Số 78 Trần Phú, Phường Lộc Thọ, Thành phố Nha Trang, Khánh Hòa'],
            ['province' => 'Khánh Hòa', 'area' => 'Thành phố Nha Trang, Khánh Hòa', 'address' => 'Số 99 Phạm Văn Đồng, Phường Vĩnh Hòa, Thành phố Nha Trang, Khánh Hòa'],

            ['province' => 'Huế', 'area' => 'Thành phố Huế, Huế', 'address' => 'Số 12 Lê Lợi, Phường Vĩnh Ninh, Thành phố Huế, Huế'],
            ['province' => 'Huế', 'area' => 'Thành phố Huế, Huế', 'address' => 'Số 45 Lê Duẩn, Phường Phú Thuận, Thành phố Huế, Huế'],

            ['province' => 'Lào Cai', 'area' => 'Thị xã Sa Pa, Lào Cai', 'address' => 'Số 10 Fansipan, Phường Sa Pa, Thị xã Sa Pa, Lào Cai'],
            ['province' => 'Lào Cai', 'area' => 'Thị xã Sa Pa, Lào Cai', 'address' => 'Số 25 Mường Hoa, Phường Sa Pa, Thị xã Sa Pa, Lào Cai'],

            ['province' => 'Ninh Bình', 'area' => 'Tràng An, Ninh Bình', 'address' => 'Khu du lịch sinh thái Tràng An, Xã Trường Yên, Huyện Hoa Lư, Ninh Bình'],
            ['province' => 'Ninh Bình', 'area' => 'Huyện Hoa Lư, Ninh Bình', 'address' => 'Số 15 đường Tràng An, Xã Ninh Xuân, Huyện Hoa Lư, Ninh Bình'],
        ];

        $descriptions = [
            'Tọa lạc ngay trung tâm thành phố, khách sạn boutique của chúng tôi là điểm khởi đầu lý tưởng để bạn khám phá mọi ngóc ngách tuyệt vời của địa phương. Phòng nghỉ thoáng đãng với view thành phố rực rỡ, dịch vụ spa thư giãn và nhà hàng ẩm thực đặc sắc. Chúng tôi không chỉ cung cấp nơi ngủ — chúng tôi tạo ra kỷ niệm.',

            'Bước vào không gian xanh mát của chúng tôi và tạm quên đi mọi bộn bề. Khu vườn nhiệt đới rộng 5.000m² bao quanh hồ bơi vô cực, được thiết kế để bạn thực sự thở sâu và nghỉ ngơi. Mỗi buổi sáng bắt đầu bằng tiếng chim hót và bữa điểm tâm được chuẩn bị tươi ngon từ nông trại địa phương.',

            'Một trong những khu nghỉ dưỡng ven sông hiếm hoi còn lại mang hồn cốt của Việt Nam xưa. Căn phòng nào cũng có view nhìn ra dòng sông êm đềm. Mỗi buổi tối thưởng thức ẩm thực đặc sản địa phương trên mặt sông trong ánh đèn lồng lung linh — đó là Việt Nam bạn luôn mơ đến.',

            'Homestay phong cách Indochine độc đáo được phục dựng nguyên vẹn trong một ngôi nhà cổ trăm tuổi. Sân trong trải dài với chậu cây đất nung và hồ cá nhỏ. Chủ nhà luôn sẵn sàng chia sẻ câu chuyện lịch sử và dẫn bạn đi những "hidden spots" mà khách du lịch thông thường không bao giờ tìm được.',

            'Sang trọng, tinh tế và gần gũi thiên nhiên — ba điều chúng tôi không bao giờ phải chọn một. Khu villa ven biển sở hữu bể bơi riêng, sân vườn trải dài và đường dẫn thẳng xuống bờ cát trắng. Nơi đây là điểm đến lý tưởng cho tuần trăng mật, kỳ nghỉ gia đình hay những cuối tuần đổi gió đặc biệt.',

            'Căn hộ dịch vụ cao cấp với thiết kế tối giản, sạch bóng mà ấm áp. Tiện nghi đầy đủ từ bếp nấu hiện đại đến bồn tắm spa, phù hợp cho những chuyến công tác dài ngày hay kỳ nghỉ "slow travel" đang ngày càng được yêu thích. Dịch vụ concierge 24/7 sẵn sàng giúp bạn đặt tour, thuê xe hay tìm nhà hàng ngon nhất gần đây.',

            'Đặt chân vào đây là bước vào một thế giới khác — nơi thời gian chậm lại và mọi giác quan được đánh thức. Khu nghỉ sinh thái nhỏ của chúng tôi ẩn sâu giữa rừng nguyên sinh, với chỉ 12 bungalow gỗ hòa mình vào thiên nhiên. Đêm nằm nghe tiếng lá rì rào và ngắm sao trời qua mái kính — đây là xa xỉ thực sự.',

            'Dành cho những người yêu sự thanh bình và trân trọng nghệ thuật. Gallery hotel của chúng tôi trưng bày tác phẩm của các nghệ sĩ địa phương trên mỗi hành lang, mỗi căn phòng. Cà phê sáng thưởng thức giữa những bức tranh sơn dầu và ánh nắng len lỏi qua ô cửa sổ — bắt đầu ngày mới theo cách không ai nghĩ đến.',

            'Khu nghỉ gia đình với những trải nghiệm không thể tìm thấy ở nơi nào khác: tự tay bắt cá tươi rồi nấu ăn cùng nhà bếp, chèo thuyền kayak khám phá hang động, leo núi ngắm bình minh và xem lại những khoảnh khắc đẹp nhất của chuyến đi bên bếp lửa trong đêm. Đây là kỳ nghỉ mà cả gia đình sẽ nhắc lại mãi.',

            'Mang vẻ đẹp của phong cách Nhật Bản đến giữa lòng Việt Nam, ryokan mini của chúng tôi tái hiện trải nghiệm onsen theo phong cách Việt Nam, với bồn tắm đá núi lửa, đèn giấy washi ấm áp và bữa ăn được phục vụ theo truyền thống kaiseki từng món một.',

            'Khách sạn boutique 5 sao nhỏ của chúng tôi chỉ có 20 phòng — đủ để chúng tôi biết tên từng vị khách, nhớ thói quen uống cà phê của bạn và chuẩn bị sẵn chăn bông ấm áp trước khi bạn về phòng vào tối khuya. Dịch vụ cá nhân hóa đến từng chi tiết nhỏ nhất.',

            'Khu căn hộ dịch vụ nằm trong tổ hợp resort ven biển với 5 hồ bơi nhiệt đới, 8 nhà hàng đặc sắc từ ẩm thực Việt, Nhật, Ý đến fusion hiện đại, spa thư giãn và trực tiếp ra bãi biển. Đây là nơi bạn sẽ không muốn rời đi.',

            'Giữa lòng phố cổ cổ kính, chúng tôi tạo ra một "thế giới song song" — vỏ ngoài là mặt tiền gạch trăm năm, bên trong là không gian hiện đại tối giản và dịch vụ không thua kém khách sạn 5 sao. Đây là địa chỉ bí mật của giới sành du lịch.',

            'Được bao quanh bởi ruộng bậc thang vàng óng vào mùa gặt và mây trắng lãng đãng quanh năm, khu nghỉ dưỡng ở độ cao 1.200m mang lại cảm giác như đang sống giữa thiên đường. Mỗi bữa sáng tự nấu bằng rau hữu cơ tươi hái từ vườn, mỗi tối ngồi bên bếp lửa nhà rông ấm áp.',

            'Một khu nghỉ ven hồ yên tĩnh với những bè nổi nhỏ xinh, nơi bạn có thể thả câu, ngồi đọc sách hay đơn giản là ngắm mây trôi cả ngày mà không cần thêm gì. Đây là nơi dành riêng cho những người cần thực sự "disconnect" khỏi thế giới bận rộn.',

            'Nhà khách nhỏ ấm cúng giữa lòng phố đêm sôi động, nhưng ngay khi bước qua cánh cổng gỗ, tất cả ồn ào tan biến. Sân trong trồng đầy hoa, tiếng đàn piano nhẹ nhàng buổi tối và chủ nhà luôn có sẵn ấm trà nóng chào đón bạn — đây là phong cách lưu trú mà bạn sẽ nhớ mãi.',
        ];

        $provinces = DB::table('provinces')->get(['id', 'name'])->keyBy('name');
        $provinceIds = $provinces->pluck('id')->toArray();
        $wardIds = DB::table('wards')->pluck('id')->toArray();

        if (empty($provinceIds)) {
            $provinceIds = range(1, 63);
        }
        if (empty($wardIds)) {
            $wardIds = range(1, 100);
        }

        $propertyTypes = DB::table('property_types')->get();
        if ($propertyTypes->isEmpty()) {
            return;
        }
        $propertyTypeIds = $propertyTypes->pluck('id')->toArray();
        $rentCategories = [1, 2, 3]; // 1: whole_unit, 2: room, 3: bed

        $typeNames = [
            'khach-san-hotel' => [
                'Khách sạn Mường Thanh Luxu', 'Sài Gòn Hotel', 'Hanoi Continental', 'Pullman Panorama', 'Rex Hotel Saigon', 
                'Novotel Suite', 'Sofitel Legend', 'Sheraton View', 'InterContinental Lake', 'Fusion Suites'
            ],
            'nha-nghi-guesthouse' => [
                'Nhà nghỉ Sen Hồng', 'Nhà nghỉ Mai Vàng', 'Guesthouse Bình Yên', 'Nhà nghỉ 24h', 'Motel Hải Yến',
                'Nhà nghỉ Cát Tường', 'Guesthouse Phố Cổ', 'Nhà nghỉ Minh Tâm', 'Nhà nghỉ Hoàn Kiếm'
            ],
            'can-ho-dich-vu-theo-phong' => [
                'Vinhomes Serviced Room', 'Masteri Suite Room', 'Sunrise City Apart', 'Ocean Park Residence',
                'Landmark 81 Serviced', 'The Manor Room', 'Ciputra Suite', 'The Nassim Room'
            ],
            'homestay-co-chia-phong' => [
                'Mây Homestay', 'Nhà của Bu', 'Lá Đỏ Homestay', 'Homestay Cỏ May', 'The Barn Homestay',
                'Little House', 'Rustic Homestay', 'Hidden Gem Homestay', 'Peaceful Corner'
            ],
        ];

        $wardsByProvince = [];

        foreach (range(1, 300) as $i) {
            $type = $faker->randomElement($propertyTypes->toArray());
            $slug = $type->slug;
            
            $location = $faker->randomElement($addressData);
            $provinceName = $location['province'];
            
            // Pick a name based on type, fallback to generic if not defined
            $possibleNames = $typeNames[$slug] ?? [
                'Tòa nhà ' . $faker->company, 'Cơ sở ' . $faker->lastName, 'Vùng ' . $faker->city, 'Khu vực ' . $faker->streetName
            ];
            
            // Filter names to prevent city mismatches (e.g., Sài Gòn Hotel in Hà Nội)
            $filteredPossibleNames = array_filter($possibleNames, function ($pName) use ($provinceName) {
                if ($provinceName !== 'Hà Nội') {
                    if (stripos($pName, 'Hanoi') !== false || stripos($pName, 'Hà Nội') !== false || stripos($pName, 'Hoàn Kiếm') !== false || stripos($pName, 'Ciputra') !== false) {
                        return false;
                    }
                }
                if ($provinceName !== 'Hồ Chí Minh') {
                    if (stripos($pName, 'Saigon') !== false || stripos($pName, 'Sài Gòn') !== false || stripos($pName, 'Landmark 81') !== false) {
                        return false;
                    }
                }
                return true;
            });

            if (empty($filteredPossibleNames)) {
                $filteredPossibleNames = $possibleNames;
            }

            $name = $faker->randomElement($filteredPossibleNames) . ' ' . $faker->randomElement(['A', 'B', 'C', 'Premium', 'Luxury', 'Plaza', 'Center', 'Garden', 'Grand', 'View', 'Park', 'Corner', 'Elite', 'Signature']);
            
            $description = $faker->randomElement($descriptions);
            
            $provinceId = $provinces->has($provinceName) ? $provinces->get($provinceName)->id : $faker->randomElement($provinceIds);

            if (!isset($wardsByProvince[$provinceId])) {
                $wardsByProvince[$provinceId] = DB::table('wards')
                    ->where('province_id', $provinceId)
                    ->pluck('id')
                    ->toArray();
            }
            $provinceWards = $wardsByProvince[$provinceId];
            $wardId = !empty($provinceWards) ? $faker->randomElement($provinceWards) : $faker->randomElement($wardIds);
            
            $address = preg_replace('/^Số \d+/', 'Số ' . rand(1, 499), $location['address']);

            DB::table('properties')->insert([
                'user_id'           => $faker->randomElement($adminPartnerIds),
                'province_id'       => $provinceId,
                'ward_id'           => $wardId,
                'name'              => $name,
                'address_detail'    => $address,
                'number_of_floors'  => $faker->numberBetween(1, 35),
                'number_of_units'   => $faker->numberBetween(10, 500),
                'year_built'        => $faker->numberBetween(1990, 2024),
                'property_type_id'  => $type->id,
                'rent_category'     => $faker->randomElement($rentCategories),
                'area'              => $faker->randomFloat(2, 1000, 50000),
                'description'       => $description,
                'created_by'        => $faker->randomElement($adminPartnerIds),
                'updated_by'        => $faker->randomElement($adminPartnerIds),
                'created_at'        => Carbon::now()->subDays(rand(1, 30)),
                'updated_at'        => Carbon::now()->subDays(rand(1, 30)),
            ]);
        }
    }
}


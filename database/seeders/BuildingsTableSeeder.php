<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuildingsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('buildings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $buildingNames = [
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
            ['area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 1 Nguyễn Huy Tưởng, Phường Thanh Xuân Trung, Quận Thanh Xuân, Hà Nội'],
            ['area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 45 Lê Văn Lương, Phường Nhân Chính, Quận Thanh Xuân, Hà Nội'],
            ['area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 75 Hoàng Đạo Thúy, Phường Nhân Chính, Quận Thanh Xuân, Hà Nội'],
            ['area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 90 Nguyễn Xiển, Phường Hạ Đình, Quận Thanh Xuân, Hà Nội'],
            ['area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 125 Khuất Duy Tiến, Phường Nhân Chính, Quận Thanh Xuân, Hà Nội'],
            ['area' => 'Quận Thanh Xuân, Hà Nội', 'address' => 'Số 200 Lê Trọng Tấn, Phường Khương Mai, Quận Thanh Xuân, Hà Nội'],
            ['area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 30 Phạm Văn Đồng, Phường Mai Dịch, Quận Cầu Giấy, Hà Nội'],
            ['area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 60 Trần Duy Hưng, Phường Trung Hòa, Quận Cầu Giấy, Hà Nội'],
            ['area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 105 Hoàng Quốc Việt, Phường Nghĩa Đô, Quận Cầu Giấy, Hà Nội'],
            ['area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 135 Cầu Giấy, Phường Quan Hoa, Quận Cầu Giấy, Hà Nội'],
            ['area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 190 Dịch Vọng, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội'],
            ['area' => 'Quận Cầu Giấy, Hà Nội', 'address' => 'Số 250 Xuân Thủy, Phường Dịch Vọng Hậu, Quận Cầu Giấy, Hà Nội'],
            ['area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 15 Nguyễn Chí Thanh, Phường Láng Thượng, Quận Đống Đa, Hà Nội'],
            ['area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 120 Đường Láng, Phường Láng Thượng, Quận Đống Đa, Hà Nội'],
            ['area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 180 Láng Hạ, Phường Láng Hạ, Quận Đống Đa, Hà Nội'],
            ['area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 220 Tây Sơn, Phường Trung Liệt, Quận Đống Đa, Hà Nội'],
            ['area' => 'Quận Đống Đa, Hà Nội', 'address' => 'Số 280 Khâm Thiên, Phường Khâm Thiên, Quận Đống Đa, Hà Nội'],
            ['area' => 'Quận Tây Hồ, Hà Nội', 'address' => 'Số 25 Võ Chí Công, Phường Xuân La, Quận Tây Hồ, Hà Nội'],
            ['area' => 'Quận Tây Hồ, Hà Nội', 'address' => 'Số 68 Lạc Long Quân, Phường Bưởi, Quận Tây Hồ, Hà Nội'],
            ['area' => 'Quận Tây Hồ, Hà Nội', 'address' => 'Số 120 Âu Cơ, Phường Nhật Tân, Quận Tây Hồ, Hà Nội'],
            ['area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 150 Phố Huế, Phường Ngô Thì Nhậm, Quận Hai Bà Trưng, Hà Nội'],
            ['area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 165 Bà Triệu, Phường Lê Đại Hành, Quận Hai Bà Trưng, Hà Nội'],
            ['area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 210 Giải Phóng, Phường Đồng Tâm, Quận Hai Bà Trưng, Hà Nội'],
            ['area' => 'Quận Hai Bà Trưng, Hà Nội', 'address' => 'Số 320 Minh Khai, Phường Minh Khai, Quận Hai Bà Trưng, Hà Nội'],
            ['area' => 'Quận Ba Đình, Hà Nội', 'address' => 'Số 195 Kim Mã, Phường Kim Mã, Quận Ba Đình, Hà Nội'],
            ['area' => 'Quận Ba Đình, Hà Nội', 'address' => 'Số 45 Nguyễn Đình Thi, Phường Giảng Võ, Quận Ba Đình, Hà Nội'],
            ['area' => 'Quận Ba Đình, Hà Nội', 'address' => 'Số 88 Đội Cấn, Phường Đội Cấn, Quận Ba Đình, Hà Nội'],
            ['area' => 'Quận Hà Đông, Hà Nội', 'address' => 'Số 225 Trần Phú, Phường Mộ Lao, Quận Hà Đông, Hà Nội'],
            ['area' => 'Quận Hà Đông, Hà Nội', 'address' => 'Số 240 Lê Trọng Tấn, Phường Dương Nội, Quận Hà Đông, Hà Nội'],
            ['area' => 'Quận Hà Đông, Hà Nội', 'address' => 'Số 350 Quang Trung, Phường Quang Trung, Quận Hà Đông, Hà Nội'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 1 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 15 Đồng Khởi, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 30 Lê Lợi, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 45 Nguyễn Du, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 60 Pasteur, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 75 Nguyễn Trung Trực, Phường Bến Thành, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 135 Lý Tự Trọng, Phường Bến Nghé, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 210 Trần Hưng Đạo, Phường Cô Giang, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 1, TP.Hồ Chí Minh', 'address' => 'Số 270 Võ Văn Kiệt, Phường Cầu Kho, Quận 1, TP.Hồ Chí Minh'],
            ['area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 90 Võ Văn Tần, Phường 6, Quận 3, TP.Hồ Chí Minh'],
            ['area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 105 Nguyễn Thị Minh Khai, Phường 6, Quận 3, TP.Hồ Chí Minh'],
            ['area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 120 Nguyễn Đình Chiểu, Phường 6, Quận 3, TP.Hồ Chí Minh'],
            ['area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 180 Võ Thị Sáu, Phường 8, Quận 3, TP.Hồ Chí Minh'],
            ['area' => 'Quận 3, TP.Hồ Chí Minh', 'address' => 'Số 225 Cách Mạng Tháng 8, Phường 10, Quận 3, TP.Hồ Chí Minh'],
            ['area' => 'Quận Bình Thạnh, TP.Hồ Chí Minh', 'address' => 'Số 150 Điện Biên Phủ, Phường 25, Quận Bình Thạnh, TP.Hồ Chí Minh'],
            ['area' => 'Quận Bình Thạnh, TP.Hồ Chí Minh', 'address' => 'Số 280 Xô Viết Nghệ Tĩnh, Phường 21, Quận Bình Thạnh, TP.Hồ Chí Minh'],
            ['area' => 'Quận Bình Thạnh, TP.Hồ Chí Minh', 'address' => 'Số 350 Nguyễn Xí, Phường 13, Quận Bình Thạnh, TP.Hồ Chí Minh'],
            ['area' => 'Quận 7, TP.Hồ Chí Minh', 'address' => 'Số 255 Nguyễn Văn Linh, Phường Tân Thuận Đông, Quận 7, TP.Hồ Chí Minh'],
            ['area' => 'Quận 7, TP.Hồ Chí Minh', 'address' => 'Số 88 Nguyễn Thị Thập, Phường Tân Phú, Quận 7, TP.Hồ Chí Minh'],
            ['area' => 'Quận 7, TP.Hồ Chí Minh', 'address' => 'Số 120 Huỳnh Tấn Phát, Phường Tân Thuận Tây, Quận 7, TP.Hồ Chí Minh'],
            ['area' => 'Quận 2, TP.Hồ Chí Minh', 'address' => 'Số 68 Nguyễn Duy Trinh, Phường Bình Trưng Đông, Quận 2, TP.Hồ Chí Minh'],
            ['area' => 'Quận 2, TP.Hồ Chí Minh', 'address' => 'Số 120 Nguyễn Thị Định, Phường An Phú, Quận 2, TP.Hồ Chí Minh'],
            ['area' => 'Quận 2, TP.Hồ Chí Minh', 'address' => 'Số 200 Mai Chí Thọ, Phường Bình An, Quận 2, TP.Hồ Chí Minh'],
            ['area' => 'Quận 9, TP.Hồ Chí Minh', 'address' => 'Số 240 Lê Văn Việt, Phường Hiệp Phú, Quận 9, TP.Hồ Chí Minh'],
            ['area' => 'Quận 9, TP.Hồ Chí Minh', 'address' => 'Số 155 Đỗ Xuân Hợp, Phường Phước Long A, Quận 9, TP.Hồ Chí Minh'],
            ['area' => 'Quận 9, TP.Hồ Chí Minh', 'address' => 'Số 320 Nguyễn Duy Trinh, Phường Tăng Nhơn Phú A, Quận 9, TP.Hồ Chí Minh'],
            ['area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 1 Trần Phú, Phường Thạch Thang, Quận Hải Châu, Đà Nẵng'],
            ['area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 15 Lê Duẩn, Phường Thạch Thang, Quận Hải Châu, Đà Nẵng'],
            ['area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 45 Hoàng Diệu, Phường Nam Dương, Quận Hải Châu, Đà Nẵng'],
            ['area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 75 Nguyễn Hữu Thọ, Phường Hòa Thuận Nam, Quận Hải Châu, Đà Nẵng'],
            ['area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 90 Đống Đa, Phường Thạch Thang, Quận Hải Châu, Đà Nẵng'],
            ['area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 105 Lý Tự Trọng, Phường Hải Châu I, Quận Hải Châu, Đà Nẵng'],
            ['area' => 'Quận Hải Châu, Đà Nẵng', 'address' => 'Số 135 Lê Đình Lý, Phường Hòa Cường Nam, Quận Hải Châu, Đà Nẵng'],
            ['area' => 'Quận Thanh Khê, Đà Nẵng', 'address' => 'Số 30 Nguyễn Văn Linh, Phường Vĩnh Trung, Quận Thanh Khê, Đà Nẵng'],
            ['area' => 'Quận Thanh Khê, Đà Nẵng', 'address' => 'Số 60 Điện Biên Phủ, Phường Thanh Khê Tây, Quận Thanh Khê, Đà Nẵng'],
            ['area' => 'Quận Thanh Khê, Đà Nẵng', 'address' => 'Số 155 Tôn Đức Thắng, Phường Thanh Khê Đông, Quận Thanh Khê, Đà Nẵng'],
            ['area' => 'Quận Ngũ Hành Sơn, Đà Nẵng', 'address' => 'Số 120 Nguyễn Chí Thanh, Phường Mỹ An, Quận Ngũ Hành Sơn, Đà Nẵng'],
            ['area' => 'Quận Ngũ Hành Sơn, Đà Nẵng', 'address' => 'Số 200 Hoàng Sa, Phường Mỹ An, Quận Ngũ Hành Sơn, Đà Nẵng'],
            ['area' => 'Quận Sơn Trà, Đà Nẵng', 'address' => 'Số 88 Võ Nguyên Giáp, Phường Mỹ An, Quận Sơn Trà, Đà Nẵng'],
            ['area' => 'Quận Sơn Trà, Đà Nẵng', 'address' => 'Số 150 Phạm Văn Đồng, Phường Phước Mỹ, Quận Sơn Trà, Đà Nẵng'],
            ['area' => 'Quận Liên Chiểu, Đà Nẵng', 'address' => 'Số 250 Nguyễn Lương Bằng, Phường Hòa Hiệp Bắc, Quận Liên Chiểu, Đà Nẵng'],
            ['area' => 'Quận Liên Chiểu, Đà Nẵng', 'address' => 'Số 320 Tôn Đức Thắng, Phường Hòa Minh, Quận Liên Chiểu, Đà Nẵng'],
        ];

        $descriptions = [
            'Tòa nhà cao cấp 25 tầng với thiết kế hiện đại, tọa lạc tại vị trí đắc địa. Bao gồm hệ thống thang máy cao tốc, bãi đỗ xe rộng rãi, hệ thống an ninh 24/7, camera giám sát toàn bộ tòa nhà. Tiện ích đầy đủ: phòng gym, bể bơi ngoài trời, sân chơi trẻ em, khu vực BBQ, vườn cảnh quan xanh mát. Gần trung tâm thương mại, trường học, bệnh viện, thuận tiện cho cuộc sống và công việc.',

            'Chung cư cao cấp 30 tầng với view thành phố tuyệt đẹp. Mỗi căn hộ được thiết kế tối ưu không gian, nội thất sang trọng. Hệ thống quản lý thông minh, thẻ từ ra vào, bảo vệ chuyên nghiệp. Tiện ích nội khu: phòng đa năng, thư viện, khu vui chơi trẻ em, phòng yoga, spa, nhà hàng. Giao thông thuận tiện, gần các tuyến metro, trạm xe buýt, dễ dàng di chuyển đến các khu vực trung tâm.',

            'Tòa nhà văn phòng kết hợp căn hộ dịch vụ 20 tầng. Tầng 1-10 là văn phòng cho thuê, tầng 11-20 là căn hộ dịch vụ cao cấp. Thiết kế hiện đại với hệ thống điều hòa trung tâm, internet tốc độ cao, hệ thống điện năng lượng mặt trời. Có bãi đỗ xe tầng hầm, thang máy riêng cho từng khu vực. Gần khu công nghiệp, trung tâm tài chính, phù hợp cho doanh nghiệp và khách công tác.',

            'Chung cư sinh thái 15 tầng với không gian xanh chiếm 40% diện tích. Thiết kế thân thiện môi trường, sử dụng năng lượng tái tạo, hệ thống xử lý nước thải hiện đại. Mỗi căn hộ có ban công rộng, có thể trồng cây xanh. Tiện ích: vườn rau sạch, khu vui chơi ngoài trời, đường chạy bộ, hồ bơi sinh thái. Gần công viên, hồ nước, không khí trong lành, phù hợp cho gia đình có trẻ em và người cao tuổi.',

            'Tòa nhà thương mại và căn hộ 18 tầng. Tầng 1-3 là trung tâm thương mại với đầy đủ tiện ích: siêu thị, nhà hàng, quán cà phê, spa, gym. Tầng 4-18 là căn hộ dịch vụ. Hệ thống an ninh hiện đại, bảo vệ 24/7, camera giám sát, thẻ từ thông minh. Thiết kế sang trọng, nội thất cao cấp. Vị trí trung tâm, gần các trường đại học, bệnh viện, ngân hàng, thuận tiện mọi mặt.',

            'Chung cư cao cấp 35 tầng với view sông/cảnh quan thành phố. Thiết kế theo tiêu chuẩn quốc tế, mỗi căn hộ có ban công rộng, cửa kính lớn lấy ánh sáng tự nhiên. Hệ thống tiện ích đẳng cấp: hồ bơi vô cực, phòng gym đầy đủ thiết bị, spa, karaoke, phòng tiệc. Quản lý chuyên nghiệp, dịch vụ 5 sao. Gần các khu vui chơi giải trí, trung tâm mua sắm cao cấp, phù hợp cho khách hàng VIP và doanh nhân.',

            'Tòa nhà căn hộ dịch vụ 22 tầng với thiết kế tối ưu cho khách công tác. Mỗi căn hộ được trang bị đầy đủ nội thất, bếp, máy giặt, wifi tốc độ cao. Dịch vụ hỗ trợ: dọn phòng hàng ngày, giặt ủi, đưa đón sân bay, đặt tour. Hệ thống an ninh nghiêm ngặt, bảo vệ 24/7. Gần sân bay, trung tâm hội nghị, khu công nghiệp, thuận tiện cho khách công tác và tổ chức sự kiện.',

            'Chung cư sinh thái thông minh 28 tầng với công nghệ nhà thông minh. Tất cả căn hộ được trang bị hệ thống điều khiển tự động: điều hòa, đèn, rèm, hệ thống an ninh. Tiện ích hiện đại: phòng gym công nghệ cao, hồ bơi thông minh, khu vui chơi trẻ em có giám sát, vườn trên cao. Hệ thống quản lý năng lượng tối ưu, tiết kiệm chi phí. Gần khu công nghệ cao, trường đại học, phù hợp cho giới trẻ và gia đình trẻ.',

            'Tòa nhà hỗn hợp 16 tầng: văn phòng, cửa hàng và căn hộ. Tầng 1-2 là cửa hàng mặt tiền, tầng 3-8 là văn phòng, tầng 9-16 là căn hộ. Thiết kế linh hoạt, có thể kết hợp văn phòng và căn hộ. Bãi đỗ xe rộng rãi, thang máy tải trọng lớn, hệ thống PCCC hiện đại. Gần chợ, trường học, bệnh viện, khu dân cư đông đúc, thuận tiện cho cả kinh doanh và sinh sống.',

            'Chung cư phục vụ người cao tuổi 12 tầng với thiết kế đặc biệt. Hành lang rộng, thang máy lớn, tay vịn ở mọi nơi, sàn chống trượt. Có phòng y tế, phòng vật lý trị liệu, khu vườn trị liệu. Dịch vụ chăm sóc sức khỏe chuyên nghiệp, bác sĩ thăm khám định kỳ. Gần bệnh viện, công viên, chợ, thuận tiện cho người cao tuổi và gia đình.',

            'Tòa nhà căn hộ cho thuê dài hạn 24 tầng. Thiết kế đơn giản, giá cả hợp lý, phù hợp cho sinh viên, công nhân, nhân viên văn phòng. Mỗi căn hộ có đầy đủ tiện nghi cơ bản. Có phòng giặt công cộng, phòng tự học, wifi miễn phí. Quản lý thân thiện, giá cả minh bạch. Gần các trường đại học, khu công nghiệp, khu văn phòng, thuận tiện cho nhiều đối tượng.',

            'Chung cư cao cấp 32 tầng với thiết kế độc đáo, kiến trúc hiện đại. Mỗi căn hộ có không gian riêng tư, ban công rộng, view đẹp. Tiện ích đầy đủ: hồ bơi vô cực, phòng gym, spa, yoga, khu vui chơi trẻ em, phòng tiệc, sân tennis. Dịch vụ quản lý chuyên nghiệp, bảo vệ 24/7, camera giám sát. Gần trung tâm thương mại, nhà hàng cao cấp, phù hợp cho khách hàng cao cấp.',

            'Tòa nhà văn phòng và căn hộ dịch vụ 19 tầng. Tầng 1-9 là văn phòng cho thuê với đầy đủ tiện ích: phòng họp, phòng tiếp khách, khu vực ăn uống. Tầng 10-19 là căn hộ dịch vụ sang trọng. Hệ thống internet tốc độ cao, điện năng lượng mặt trời, hệ thống làm mát tiết kiệm năng lượng. Bãi đỗ xe tầng hầm, thang máy riêng. Gần trung tâm tài chính, sân bay, phù hợp cho doanh nghiệp và khách công tác.',

            'Chung cư sinh thái và bền vững 14 tầng. Thiết kế xanh với hệ thống thu nước mưa, năng lượng mặt trời, vườn trên mái. Không gian xanh chiếm 50% diện tích với vườn cây, hồ nước, đường đi bộ. Tiện ích: vườn rau sạch, khu vui chơi tự nhiên, phòng đa năng. Gần công viên lớn, hồ nước, không khí trong lành, phù hợp cho những người yêu thiên nhiên và môi trường.',

            'Tòa nhà căn hộ dịch vụ ngắn hạn 26 tầng. Thiết kế hiện đại, mỗi căn hộ được trang bị đầy đủ nội thất, thiết bị điện tử. Dịch vụ đầy đủ: dọn phòng, giặt ủi, đưa đón, đặt tour, đặt nhà hàng. Hệ thống đặt phòng online, check-in nhanh chóng. Gần các điểm du lịch, trung tâm mua sắm, nhà hàng, phù hợp cho khách du lịch và công tác ngắn hạn.',
        ];

        $provinceIds = DB::table('provinces')->pluck('id')->toArray();
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

        foreach (range(1, 50) as $i) {
            $type = $faker->randomElement($propertyTypes->toArray());
            $slug = $type->slug;
            
            // Pick a name based on type, fallback to generic if not defined
            $possibleNames = $typeNames[$slug] ?? [
                'Tòa nhà ' . $faker->company, 'Cơ sở ' . $faker->lastName, 'Vùng ' . $faker->city, 'Khu vực ' . $faker->streetName
            ];
            $name = $faker->randomElement($possibleNames);
            
            $location = $faker->randomElement($addressData);
            $description = $faker->randomElement($descriptions);

            DB::table('buildings')->insert([
                'user_id'           => $faker->randomElement($adminPartnerIds),
                'province_id'       => $faker->randomElement($provinceIds),
                'ward_id'           => $faker->randomElement($wardIds),
                'name'              => $name,
                'address_detail'    => $location['address'],
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

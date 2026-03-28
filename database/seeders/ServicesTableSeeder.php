<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $services = [
            [
                'name'        => 'WiFi Internet tốc độ cao',
                'price'       => 0,
                'description' => 'Dịch vụ WiFi miễn phí tốc độ cao 100Mbps, phủ sóng toàn bộ tòa nhà. Hỗ trợ đa thiết bị, kết nối ổn định 24/7. Không giới hạn dung lượng, phù hợp cho công việc và giải trí.',
            ],
            [
                'name'        => 'Điện nước cơ bản',
                'price'       => 0,
                'description' => 'Dịch vụ điện nước cơ bản được bao gồm trong giá phòng. Hệ thống điện ổn định, nước nóng lạnh 24/7. Không giới hạn sử dụng trong phạm vi hợp lý.',
            ],
            [
                'name'        => 'Vệ sinh phòng hàng ngày',
                'price'       => 0,
                'description' => 'Dọn phòng hàng ngày từ 9h-11h sáng. Bao gồm: thay ga gối, dọn dẹp phòng, vệ sinh phòng tắm, thay khăn tắm, bổ sung đồ vệ sinh cá nhân. Dịch vụ miễn phí cho khách lưu trú.',
            ],
            [
                'name'        => 'Bảo vệ 24/7',
                'price'       => 0,
                'description' => 'Hệ thống bảo vệ chuyên nghiệp 24/7 với camera giám sát toàn bộ tòa nhà. Bảo vệ túc trực tại cổng, thang máy, khu vực chung. Đảm bảo an ninh và an toàn cho khách hàng.',
            ],
            [
                'name'        => 'Thang máy',
                'price'       => 0,
                'description' => 'Hệ thống thang máy hiện đại, hoạt động 24/7. Thang máy cao tốc, an toàn, có camera giám sát. Phục vụ tất cả các tầng, tiện lợi cho việc di chuyển.',
            ],
            [
                'name'        => 'Bãi đỗ xe',
                'price'       => 0,
                'description' => 'Bãi đỗ xe miễn phí cho khách lưu trú. Bãi đỗ xe tầng hầm và ngoài trời, có bảo vệ giám sát. Mỗi phòng được cấp 1 chỗ đỗ xe miễn phí.',
            ],
            [
                'name'        => 'Tư vấn du lịch',
                'price'       => 0,
                'description' => 'Dịch vụ tư vấn du lịch miễn phí tại quầy lễ tân. Tư vấn về các điểm tham quan, nhà hàng, quán cà phê, địa điểm vui chơi trong khu vực. Cung cấp bản đồ và hướng dẫn chi tiết.',
            ],
            [
                'name'        => 'Dịch vụ giặt ủi',
                'price'       => 50000,
                'description' => 'Dịch vụ giặt ủi chuyên nghiệp. Lấy đồ vào buổi sáng (8h-10h), trả vào buổi chiều (16h-18h). Giặt khô, giặt ướt, ủi phẳng. Giá từ 50.000đ/kg, tối thiểu 2kg. Dịch vụ nhanh (24h) có phụ phí 30%.',
            ],
            [
                'name'        => 'Đưa đón sân bay',
                'price'       => 350000,
                'description' => 'Dịch vụ đưa đón sân bay chuyên nghiệp. Xe 4-7 chỗ, có người cầm bảng tên đón tại sân bay. Giá một chiều 350.000đ, khứ hồi 600.000đ. Xe 16 chỗ: một chiều 500.000đ, khứ hồi 900.000đ. Cần đặt trước ít nhất 24h.',
            ],
            [
                'name'        => 'Bữa sáng buffet',
                'price'       => 150000,
                'description' => 'Bữa sáng buffet phong phú từ 6h30-10h sáng hàng ngày. Menu đa dạng: món Á, món Âu, đồ uống, trái cây, bánh ngọt. Giá 150.000đ/người, trẻ em dưới 6 tuổi miễn phí, 6-12 tuổi giảm 50%. Có thể đặt theo ngày hoặc theo kỳ.',
            ],
            [
                'name'        => 'Dịch vụ massage tại phòng',
                'price'       => 400000,
                'description' => 'Dịch vụ massage chuyên nghiệp tại phòng. Massage body, foot massage, massage đầu. Thời gian 60 phút, giá 400.000đ. Thời gian 90 phút, giá 550.000đ. Cần đặt trước ít nhất 2 giờ. Có massage đôi, giá ưu đãi.',
            ],
            [
                'name'        => 'Dịch vụ spa',
                'price'       => 800000,
                'description' => 'Dịch vụ spa đầy đủ: tắm hơi, xông hơi, tắm bùn, chăm sóc da mặt, chăm sóc body. Gói cơ bản 800.000đ (90 phút), gói cao cấp 1.500.000đ (150 phút). Cần đặt trước ít nhất 1 ngày. Có gói dành cho cặp đôi.',
            ],
            [
                'name'        => 'Phòng gym và fitness',
                'price'       => 100000,
                'description' => 'Phòng gym hiện đại với đầy đủ thiết bị: máy chạy bộ, xe đạp, tạ, máy tập các nhóm cơ. Mở cửa 6h-22h hàng ngày. Vé ngày 100.000đ, vé tuần 500.000đ, vé tháng 1.500.000đ. Có huấn luyện viên cá nhân, giá riêng.',
            ],
            [
                'name'        => 'Tour du lịch trong ngày',
                'price'       => 500000,
                'description' => 'Tour du lịch trong ngày đến các điểm tham quan nổi tiếng. Bao gồm: xe đưa đón, hướng dẫn viên, vé tham quan, bữa trưa. Giá từ 500.000đ/người tùy địa điểm. Tour nhóm ưu đãi giảm 10-20%. Cần đặt trước ít nhất 1 ngày.',
            ],
            [
                'name'        => 'Dịch vụ đặt xe taxi/Grab',
                'price'       => 0,
                'description' => 'Dịch vụ hỗ trợ đặt xe taxi, Grab, xe công nghệ. Quầy lễ tân hỗ trợ gọi xe 24/7. Không tính phí dịch vụ, khách chỉ trả phí vận chuyển theo bảng giá. Có thể đặt trước cho các chuyến đi xa.',
            ],
            [
                'name'        => 'Dịch vụ giữ trẻ',
                'price'       => 200000,
                'description' => 'Dịch vụ giữ trẻ chuyên nghiệp với bảo mẫu có kinh nghiệm. Giữ trẻ tại phòng hoặc khu vui chơi trẻ em. Giá 200.000đ/giờ, tối thiểu 2 giờ. Có dịch vụ qua đêm, giá riêng. Cần đặt trước ít nhất 6 giờ.',
            ],
            [
                'name'        => 'Dọn phòng thêm',
                'price'       => 100000,
                'description' => 'Dịch vụ dọn phòng thêm ngoài giờ dọn phòng hàng ngày. Dọn phòng vào buổi chiều hoặc tối theo yêu cầu. Giá 100.000đ/lần. Bao gồm: thay ga gối, dọn dẹp, vệ sinh phòng tắm, bổ sung đồ dùng.',
            ],
            [
                'name'        => 'Vật dụng phòng thêm',
                'price'       => 50000,
                'description' => 'Dịch vụ cung cấp vật dụng phòng thêm: gối, chăn, khăn tắm, đồ vệ sinh cá nhân, máy sấy tóc, bàn ủi. Giá từ 50.000đ/món. Có thể yêu cầu qua điện thoại, giao đến phòng trong 30 phút.',
            ],
            [
                'name'        => 'Room service - Gọi món tại phòng',
                'price'       => 0,
                'description' => 'Dịch vụ gọi món tại phòng 24/7. Menu đa dạng: món Á, món Âu, đồ uống, trà cà phê, đồ ăn nhẹ. Khách chỉ trả giá món ăn, không tính phí dịch vụ. Giao đến phòng trong 20-40 phút. Có thể thanh toán tại phòng hoặc quầy.',
            ],
            [
                'name'        => 'Dịch vụ tổ chức tiệc/Karaoke',
                'price'       => 2000000,
                'description' => 'Dịch vụ tổ chức tiệc, sinh nhật, karaoke tại phòng hoặc phòng chức năng. Bao gồm: trang trí, âm thanh, ánh sáng, bàn ghế. Giá từ 2.000.000đ/buổi (4 giờ). Có thể đặt thêm đồ ăn, đồ uống. Cần đặt trước ít nhất 3 ngày.',
            ],
            [
                'name'        => 'Thuê xe máy',
                'price'       => 150000,
                'description' => 'Dịch vụ thuê xe máy tự lái. Xe số và xe tay ga, đầy đủ giấy tờ, bảo hiểm. Giá từ 150.000đ/ngày (24h), 500.000đ/tuần. Cần để lại CMND/CCCD và đặt cọc. Có thể thuê theo giờ, giá riêng.',
            ],
            [
                'name'        => 'Dịch vụ y tế',
                'price'       => 300000,
                'description' => 'Dịch vụ y tế tại phòng: khám bệnh, tiêm thuốc, đo huyết áp, đo đường huyết. Bác sĩ đến tận phòng. Giá khám 300.000đ/lần. Có dịch vụ cấp cứu 24/7. Hỗ trợ mua thuốc, đưa đến bệnh viện khi cần.',
            ],
            [
                'name'        => 'Dịch vụ hướng dẫn viên',
                'price'       => 800000,
                'description' => 'Dịch vụ hướng dẫn viên du lịch chuyên nghiệp. Hướng dẫn tham quan các điểm du lịch, tư vấn ẩm thực, mua sắm. Giá 800.000đ/ngày (8 giờ), hỗ trợ tiếng Việt, tiếng Anh. Có thể yêu cầu hướng dẫn viên nói các ngôn ngữ khác, giá riêng.',
            ],
            [
                'name'        => 'Dịch vụ đặt vé máy bay/tàu',
                'price'       => 50000,
                'description' => 'Dịch vụ hỗ trợ đặt vé máy bay, tàu hỏa, xe khách. Quầy lễ tân hỗ trợ tìm kiếm và đặt vé. Phí dịch vụ 50.000đ/lượt. Khách trả giá vé theo bảng giá của hãng. Có thể đặt trước, thanh toán tại quầy.',
            ],
            [
                'name'        => 'Dịch vụ in ấn và photocopy',
                'price'       => 5000,
                'description' => 'Dịch vụ in ấn và photocopy tài liệu. In màu 5.000đ/trang, in đen trắng 2.000đ/trang. Photocopy 1.000đ/trang. Có thể in từ email, USB, điện thoại. Dịch vụ tại quầy lễ tân, phục vụ 24/7.',
            ],
            [
                'name'        => 'Dịch vụ bể bơi',
                'price'       => 100000,
                'description' => 'Sử dụng bể bơi ngoài trời hoặc trong nhà. Bể bơi có mái che, có chỗ tắm nắng. Giá 100.000đ/người/ngày, trẻ em dưới 12 tuổi miễn phí. Có phao bơi, khăn tắm cho thuê. Mở cửa 6h-22h hàng ngày.',
            ],
            [
                'name'        => 'Dịch vụ hội nghị/Phòng họp',
                'price'       => 500000,
                'description' => 'Thuê phòng họp, phòng hội nghị với đầy đủ tiện nghi: máy chiếu, màn hình, âm thanh, wifi, bảng viết. Giá từ 500.000đ/giờ, tối thiểu 2 giờ. Có phòng họp nhỏ (10-20 người) và lớn (50-100 người). Có thể đặt thêm dịch vụ ăn uống.',
            ],
            [
                'name'        => 'Dịch vụ trông giữ hành lý',
                'price'       => 50000,
                'description' => 'Dịch vụ trông giữ hành lý an toàn tại quầy lễ tân. Giữ hành lý trước khi check-in hoặc sau khi check-out. Miễn phí trong 24h đầu, sau đó 50.000đ/ngày. Có camera giám sát, an toàn tuyệt đối.',
            ],
            [
                'name'        => 'Dịch vụ đổi tiền',
                'price'       => 0,
                'description' => 'Dịch vụ đổi tiền tệ tại quầy lễ tân. Hỗ trợ đổi các loại tiền tệ phổ biến: USD, EUR, JPY, CNY, SGD, THB. Tỷ giá cạnh tranh, cập nhật hàng ngày. Không tính phí dịch vụ, chỉ áp dụng tỷ giá hối đoái.',
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->insert([
                'name'        => $service['name'],
                'price'       => $service['price'],
                'description' => $service['description'],
                'created_by'  => $faker->randomElement($adminPartnerIds),
                'updated_by'  => $faker->randomElement($adminPartnerIds),
                'created_at'  => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'  => Carbon::now()->subDays(rand(1, 50)),
            ]);
        }
    }
}

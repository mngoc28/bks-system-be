<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('rooms')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $descriptions = [
            'Phòng Studio hiện đại với diện tích 25m², thiết kế tối ưu không gian. Phòng được trang bị đầy đủ: giường đôi, tủ quần áo, bàn làm việc, ghế văn phòng, TV 43 inch Smart TV. Khu vực bếp mini với tủ lạnh, lò vi sóng, bếp từ, bồn rửa. Phòng tắm riêng với vòi sen hiện đại, máy sấy tóc. Cửa sổ lớn lấy ánh sáng tự nhiên, view thành phố. Internet wifi tốc độ cao, điều hòa không khí inverter tiết kiệm điện. Phù hợp cho 1-2 người, lý tưởng cho khách công tác hoặc cặp đôi.',

            'Phòng Studio cao cấp 30m² với thiết kế mở rộng không gian. Nội thất gỗ tự nhiên sang trọng, giường King size, sofa bed có thể chuyển thành giường phụ. Khu vực bếp đầy đủ tiện nghi: tủ lạnh mini, lò nướng, máy pha cà phê, bếp từ 2 lò. Phòng tắm rộng với bồn tắm và vòi sen, đồ vệ sinh cao cấp. Ban công riêng, view hướng đông đón nắng sáng. Smart TV 55 inch, hệ thống loa Bluetooth, đèn LED điều chỉnh độ sáng. Internet 1Gbps, điều hòa 2 chiều. Phù hợp cho gia đình nhỏ hoặc khách công tác dài ngày.',
            'Phòng 1 phòng ngủ rộng rãi 35m² với thiết kế hiện đại, tách biệt phòng ngủ và phòng khách. Phòng ngủ có giường đôi lớn, tủ quần áo 3 cánh, bàn trang điểm. Phòng khách với sofa bọc da, bàn trà, TV 55 inch Smart TV, kệ sách. Bếp đầy đủ: tủ lạnh side-by-side, lò vi sóng, lò nướng, bếp từ, máy rửa bát. Phòng tắm rộng với bồn tắm, vòi sen massage, bàn chải răng điện. Cửa sổ kính 2 lớp cách âm, cách nhiệt, ban công rộng view đẹp. Hệ thống điều hòa trung tâm, sàn gỗ, trần thạch cao. Phù hợp cho gia đình 2-3 người.',

            'Phòng 1 phòng ngủ cao cấp 40m² với thiết kế mở, không gian thoáng đãng. Phòng ngủ riêng biệt với giường King size, tủ quần áo tích hợp, bàn làm việc. Khu vực phòng khách rộng với sofa góc, bàn ăn 4 người, TV 65 inch Smart TV 4K. Bếp hiện đại: tủ lạnh lớn, máy rửa bát, máy pha cà phê espresso, lò nướng convection. Phòng tắm luxury với bồn tắm jacuzzi, vòi sen mưa, sàn sưởi. Ban công lớn với bàn ghế ngoài trời, view toàn cảnh thành phố. Smart home: điều khiển đèn, rèm, điều hòa qua điện thoại. Internet tốc độ cao, sàn gỗ cao cấp. Phù hợp cho khách VIP hoặc gia đình nhỏ.',
            'Phòng 2 phòng ngủ rộng 50m², thiết kế tối ưu cho gia đình. Phòng ngủ chính có giường King size, tủ quần áo lớn, bàn trang điểm. Phòng ngủ phụ có 2 giường đơn, tủ quần áo, bàn học. Phòng khách rộng với sofa chữ L, bàn trà, TV 65 inch Smart TV, kệ ti vi. Bếp đầy đủ: tủ lạnh lớn, máy rửa bát, lò vi sóng, lò nướng, bếp từ 4 lò, máy hút mùi. Phòng tắm chính với bồn tắm, phòng tắm phụ với vòi sen. Ban công rộng với bàn ghế, view hướng nam. Điều hòa multi-split, sàn gỗ, cửa sổ kính an toàn. Phù hợp cho gia đình 4-5 người.',

            'Phòng 2 phòng ngủ cao cấp 60m² với thiết kế sang trọng. Phòng ngủ master có giường King size, tủ quần áo walk-in, phòng tắm riêng với bồn tắm jacuzzi. Phòng ngủ phụ có giường đôi, tủ quần áo, bàn làm việc. Phòng khách lớn với sofa da cao cấp, bàn ăn 6 người, TV 75 inch Smart TV 4K, kệ rượu. Bếp hiện đại: tủ lạnh side-by-side, máy rửa bát, máy pha cà phê, lò nướng, bếp từ 5 lò. 2 phòng tắm đầy đủ tiện nghi. Ban công lớn với không gian thư giãn, view đẹp. Hệ thống smart home, điều hòa trung tâm, sàn gỗ cao cấp. Phù hợp cho gia đình lớn hoặc khách VIP.',
            'Phòng Studio góc 28m² với 2 mặt cửa sổ, view đẹp 2 hướng. Thiết kế hiện đại với nội thất tối giản, giường đôi, tủ quần áo tích hợp, bàn làm việc. Khu vực bếp mini: tủ lạnh, lò vi sóng, bếp từ, máy pha cà phê. Phòng tắm với vòi sen massage, máy sấy tóc. Cửa sổ kính lớn, ban công nhỏ, ánh sáng tự nhiên dồi dào. Smart TV 50 inch, hệ thống loa, đèn LED RGB. Internet tốc độ cao, điều hòa inverter. Phù hợp cho khách công tác hoặc cặp đôi thích không gian sáng.',

            'Phòng Studio hướng tây 26m² với view hoàng hôn tuyệt đẹp. Thiết kế ấm cúng với giường đôi, sofa bed, tủ quần áo, bàn làm việc góc. Bếp mini đầy đủ: tủ lạnh, lò vi sóng, bếp từ, ấm đun nước. Phòng tắm hiện đại với vòi sen mưa, đồ vệ sinh cao cấp. Ban công riêng hướng tây, view thành phố về đêm. Smart TV 48 inch, hệ thống ánh sáng thông minh. Internet tốc độ cao, điều hòa 2 chiều. Phù hợp cho khách du lịch hoặc công tác ngắn hạn.',

            'Phòng 1 phòng ngủ hướng đông 38m² đón nắng sáng. Phòng ngủ riêng với giường Queen size, tủ quần áo, bàn trang điểm có đèn. Phòng khách với sofa bọc vải cao cấp, bàn làm việc, TV 55 inch Smart TV. Bếp đầy đủ: tủ lạnh, máy rửa bát, lò vi sóng, lò nướng, bếp từ. Phòng tắm rộng với bồn tắm và vòi sen, sàn gỗ chống trượt. Cửa sổ lớn hướng đông, ban công với cây xanh, không khí trong lành. Điều hòa inverter, sàn gỗ, cửa kính an toàn. Phù hợp cho gia đình nhỏ hoặc khách công tác.',

            'Phòng 1 phòng ngủ góc tầng cao 42m² với view toàn cảnh. Phòng ngủ master với giường King size, tủ quần áo walk-in, bàn làm việc riêng. Phòng khách rộng với sofa góc, bàn ăn, TV 65 inch Smart TV 4K, kệ trang trí. Bếp hiện đại: tủ lạnh lớn, máy rửa bát, máy pha cà phê, lò nướng, bếp từ 4 lò. Phòng tắm luxury với bồn tắm góc, vòi sen mưa, sàn sưởi. Ban công lớn với view 360 độ, không gian thư giãn tuyệt vời. Smart home system, điều hòa trung tâm, sàn gỗ cao cấp. Phù hợp cho khách VIP hoặc gia đình nhỏ.',
            'Phòng Studio view sông 32m² với cảnh quan tuyệt đẹp. Thiết kế mở với giường đôi, khu vực làm việc, tủ quần áo tích hợp. Bếp mini: tủ lạnh, lò vi sóng, bếp từ, máy pha cà phê. Phòng tắm với vòi sen hiện đại, đồ vệ sinh cao cấp. Cửa sổ kính lớn view sông, ban công riêng, không gian yên tĩnh. Smart TV 50 inch, hệ thống loa, đèn LED. Internet tốc độ cao, điều hòa inverter. Phù hợp cho khách muốn không gian yên tĩnh và view đẹp.',

            'Phòng 1 phòng ngủ view biển 45m² với thiết kế hiện đại. Phòng ngủ riêng với giường Queen size, tủ quần áo, bàn làm việc. Phòng khách rộng với sofa, bàn ăn, TV 60 inch Smart TV, kệ sách. Bếp đầy đủ: tủ lạnh, máy rửa bát, lò vi sóng, lò nướng, bếp từ. Phòng tắm với bồn tắm và vòi sen, cửa sổ kính. Ban công lớn view biển, bàn ghế ngoài trời, không gian thư giãn tuyệt vời. Điều hòa multi-split, sàn gỗ, cửa kính an toàn. Phù hợp cho khách du lịch hoặc nghỉ dưỡng.',
            'Phòng Studio thông minh 27m² với công nghệ smart home. Giường đôi có thể điều chỉnh, tủ quần áo thông minh, bàn làm việc có sạc không dây. Bếp mini: tủ lạnh thông minh, lò vi sóng, bếp từ cảm ứng, máy pha cà phê tự động. Phòng tắm với vòi sen massage, gương thông minh, sàn sưởi. Cửa sổ thông minh tự động điều chỉnh, ban công nhỏ. Smart TV 55 inch, hệ thống loa surround, đèn LED RGB. Internet 1Gbps, điều hòa thông minh. Điều khiển tất cả qua điện thoại. Phù hợp cho người yêu công nghệ.',

            'Phòng 1 phòng ngủ sinh thái 36m² với thiết kế xanh. Phòng ngủ với giường đôi, tủ quần áo gỗ tự nhiên, bàn làm việc. Phòng khách với sofa bọc vải hữu cơ, bàn ăn gỗ, TV 55 inch Smart TV. Bếp với tủ lạnh tiết kiệm năng lượng, máy rửa bát, lò vi sóng, bếp từ. Phòng tắm với vòi sen tiết kiệm nước, đồ vệ sinh thân thiện môi trường. Ban công với vườn cây nhỏ, không gian xanh. Hệ thống năng lượng mặt trời, điều hòa inverter, sàn gỗ tái chế. Phù hợp cho người quan tâm môi trường.',
            'Phòng Studio phong cách minimalist 24m² với thiết kế tối giản. Giường đôi thấp, tủ quần áo tích hợp tường, bàn làm việc gỗ. Khu vực bếp mini: tủ lạnh, lò vi sóng, bếp từ, máy pha cà phê. Phòng tắm tối giản với vòi sen, đồ vệ sinh đơn giản. Cửa sổ lớn, không gian mở, ánh sáng tự nhiên. Smart TV 48 inch, hệ thống loa ẩn, đèn LED ẩn. Internet tốc độ cao, điều hòa inverter. Phù hợp cho người thích không gian tối giản, hiện đại.',

            'Phòng 1 phòng ngủ phong cách cổ điển 44m² với nội thất sang trọng. Phòng ngủ với giường Queen size có chân, tủ quần áo gỗ cổ điển, bàn trang điểm. Phòng khách với sofa bọc da, bàn ăn gỗ, TV 60 inch Smart TV, kệ sách cổ điển. Bếp với tủ lạnh, máy rửa bát, lò vi sóng, lò nướng, bếp từ. Phòng tắm với bồn tắm chân, vòi sen cổ điển, đồ vệ sinh cao cấp. Cửa sổ kính, ban công với lan can cổ điển. Điều hòa trung tâm, sàn gỗ cổ điển. Phù hợp cho khách thích phong cách cổ điển.',
            'Phòng Studio loft 30m² với trần cao 4m, không gian rộng thoáng. Giường đôi trên tầng, khu vực làm việc dưới, tủ quần áo tích hợp. Bếp mini: tủ lạnh, lò vi sóng, bếp từ, máy pha cà phê. Phòng tắm với vòi sen, đồ vệ sinh hiện đại. Cửa sổ lớn, không gian mở, ánh sáng tự nhiên dồi dào. Smart TV 52 inch, hệ thống loa, đèn LED. Internet tốc độ cao, điều hòa inverter. Phù hợp cho người thích không gian mở, độc đáo.',

            'Phòng 1 phòng ngủ với phòng khách mở 40m². Phòng ngủ riêng với giường Queen size, tủ quần áo, bàn làm việc. Phòng khách mở với sofa chữ L, bàn ăn, TV 58 inch Smart TV, kệ ti vi. Bếp mở: tủ lạnh, máy rửa bát, lò vi sóng, lò nướng, bếp từ. Phòng tắm với bồn tắm và vòi sen. Không gian mở, ánh sáng tự nhiên, view đẹp. Điều hòa multi-split, sàn gỗ, cửa kính. Phù hợp cho gia đình nhỏ hoặc khách công tác.',
            'Phòng Studio premium 29m² với tiện nghi cao cấp. Giường đôi điều chỉnh, tủ quần áo có sấy, bàn làm việc có sạc không dây. Bếp mini: tủ lạnh thông minh, lò vi sóng, bếp từ cảm ứng, máy pha cà phê espresso. Phòng tắm với vòi sen mưa, gương thông minh, sàn sưởi, máy sấy tóc. Ban công riêng, view đẹp. Smart TV 55 inch 4K, hệ thống loa surround, đèn LED RGB. Internet 1Gbps, điều hòa thông minh. Phù hợp cho khách VIP.',

            'Phòng 1 phòng ngủ deluxe 46m² với tiện nghi đẳng cấp. Phòng ngủ master với giường King size, tủ quần áo walk-in, bàn trang điểm có đèn. Phòng khách với sofa da cao cấp, bàn ăn, TV 70 inch Smart TV 4K, kệ rượu. Bếp hiện đại: tủ lạnh side-by-side, máy rửa bát, máy pha cà phê, lò nướng, bếp từ 5 lò. Phòng tắm luxury với bồn tắm jacuzzi, vòi sen mưa, sàn sưởi, gương thông minh. Ban công lớn với không gian thư giãn, view toàn cảnh. Smart home system, điều hòa trung tâm, sàn gỗ cao cấp. Phù hợp cho khách VIP hoặc gia đình cao cấp.',
        ];

        $roomTitles = [
            'Phòng Studio Cao Cấp',
            'Phòng Studio Sang Trọng',
            'Phòng Studio Hiện Đại',
            'Phòng Studio Tiện Nghi',
            'Căn Hộ Mini Cao Cấp',
            'Căn Hộ Mini Hiện Đại',
            'Căn Hộ 1 Phòng Ngủ Luxury',
            'Căn Hộ 1 Phòng Ngủ Tiện Nghi',
            'Căn Hộ 2 Phòng Ngủ Gia Đình',
            'Căn Hộ 2 Phòng Ngủ Rộng Rãi',
            'Suite Hoàng Gia',
            'Suite Sang Trọng',
            'Phòng Suite View Sông',
            'Phòng Suite Tầng Cao',
            'Phòng Studio Tầng Mái',
            'Căn Hộ Penthouse Đẳng Cấp',
            'Phòng Studio Hướng Đông',
            'Phòng Studio Hướng Tây',
            'Căn Hộ Dịch Vụ Cao Cấp',
            'Căn Hộ Dịch Vụ Tiện Nghi',
        ];

        $buildingIds = DB::table('buildings')->pluck('id')->toArray();
        if (empty($buildingIds)) {
            $buildingIds = range(1, 50);
        }

        foreach (range(1, 100) as $i) {
            $description = $faker->randomElement($descriptions);
            $title = $faker->randomElement($roomTitles) . ' ' . $i;
            
            // Determine room_type and people based on type
            $roomType = $faker->numberBetween(1, 3); // 1=Studio, 2=Double, 3=Mini apartment
            $people = match ($roomType) {
                1 => 1,                           // Studio: 1 person max
                2 => 2,                           // Double room: 2 people
                3 => $faker->numberBetween(5, 10), // Mini apartment: 5-10 people
                default => 1,
            };

            DB::table('rooms')->insert([
                'building_id' => $faker->randomElement($buildingIds),
                'title' => $title,
                'room_number' => 'R' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'deposit' => $faker->numberBetween(1000000, 5000000),
                'area' => $faker->randomFloat(2, 15, 50),
                'floor_number' => $faker->numberBetween(1, 30),
                'people' => $people,
                'room_type' => $roomType, // 1=Studio, 2=Double, 3=Mini apartment
                'status' => $faker->boolean(70), // 70% available (true), 30% booked/maintenance (false)
                'description' => $description,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now()->subDays(rand(1, 50)),
                'updated_at' => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }
    }
}

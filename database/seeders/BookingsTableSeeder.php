<?php
namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('bookings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $userIds = DB::table('users')
            ->where('role', 'user')
            ->where('id', '!=', 1)
            ->pluck('id')
            ->toArray();

        if (empty($userIds)) {
            $userIds = range(2, 100);
        }

        $roomIds  = DB::table('rooms')->pluck('id')->toArray();
        $priceIds = DB::table('room_prices')->pluck('id')->toArray();

        if (empty($roomIds) || empty($priceIds)) {
            $this->command->warn('No rooms or room_prices found. Please run RoomsTableSeeder and RoomPricesTableSeeder first.');
            return;
        }

        $noteTemplates = [
            'Yêu cầu phòng hướng nam, có ban công, tầng cao từ 10 trở lên để có view đẹp',
            'Cần phòng cách âm tốt, yên tĩnh, tránh khu vực gần thang máy hoặc lối đi',
            'Yêu cầu phòng có giường đôi, không hút thuốc, có cửa sổ lớn',
            'Cần phòng gần lối ra vào, tầng thấp (tầng 1-3), tiện cho người cao tuổi',
            'Yêu cầu phòng có view hướng biển/sông, tầng cao, có ban công rộng',
            'Cần phòng có đầy đủ tiện nghi: tủ lạnh, bếp mini, máy lạnh hoạt động tốt',
            'Yêu cầu phòng có giường phụ, phù hợp cho gia đình có trẻ em',
            'Cần phòng có internet tốc độ cao, ổ cắm điện đầy đủ, phù hợp cho làm việc',
            'Đặt phòng cho chuyến công tác dài ngày, cần phòng yên tĩnh để làm việc',
            'Nghỉ dưỡng cuối tuần, mong muốn có không gian thoải mái, view đẹp',
            'Tổ chức hội nghị nhỏ, cần phòng có không gian rộng, tiện cho họp nhóm',
            'Đặt phòng cho khách nước ngoài, cần hỗ trợ tiếng Anh, có wifi tốt',
            'Lưu trú dài hạn cho nhân viên công ty, yêu cầu giá cả hợp lý',
            'Đặt phòng cho đám cưới, cần phòng trang trí đẹp, có không gian chụp ảnh',
            'Nghỉ dưỡng cùng gia đình, có 2 trẻ em, cần giường phụ và nôi trẻ em',
            'Chuyến du lịch cá nhân, muốn khám phá khu vực xung quanh, cần tư vấn tour',
            'Yêu cầu bữa sáng buffet từ 7h-9h, có đồ ăn chay, không có đậu phộng',
            'Cần dịch vụ đưa đón sân bay, xe 7 chỗ, có người cầm bảng tên',
            'Yêu cầu dọn phòng hàng ngày lúc 10h sáng, thay khăn tắm mỗi ngày',
            'Cần dịch vụ giặt ủi nhanh, có thể lấy đồ vào buổi sáng và trả vào buổi chiều',
            'Yêu cầu dịch vụ massage tại phòng vào buổi tối, cần đặt trước 1 ngày',
            'Cần hỗ trợ đặt bàn nhà hàng, đặt tour du lịch, đặt vé xem show',
            'Yêu cầu dịch vụ phòng 24/7, có thể gọi món ăn bất kỳ lúc nào',
            'Cần dịch vụ spa, gym, bể bơi, mong muốn được tư vấn về các gói dịch vụ',
            'Khách có dị ứng với bụi và phấn hoa, cần phòng được vệ sinh kỹ, không có hoa tươi',
            'Khách là người khuyết tật đi lại, cần phòng có thiết bị hỗ trợ, lối đi rộng',
            'Khách hàng ăn chay trường, cần menu chay phong phú, không có thịt/cá',
            'Có trẻ sơ sinh, cần nôi trẻ em, ghế ăn, không gian an toàn cho trẻ',
            'Khách hàng là người nước ngoài, không biết tiếng Việt, cần hỗ trợ dịch thuật',
            'Yêu cầu check-in sớm lúc 12h trưa thay vì 14h, phòng đã được chuẩn bị sẵn',
            'Check-out muộn đến 15h chiều, có thể gia hạn thêm 1 giờ nếu cần',
            'Khách hàng VIP, cần phòng đặc biệt, có hoa quả và đồ uống chào đón',
            'Có lịch họp quan trọng vào sáng ngày mai, cần phòng yên tĩnh, không bị làm phiền',
            'Sẽ có bạn bè đến thăm, cần phòng có không gian tiếp khách, có thêm ghế',
            'Có sự kiện đặc biệt vào ngày ở, cần trang trí phòng theo yêu cầu',
            'Lịch trình dày đặc, cần phòng gần thang máy để tiết kiệm thời gian',
            'Có đặt tour du lịch vào buổi sáng, cần gọi thức dậy sớm lúc 6h',
            'Sẽ tổ chức tiệc nhỏ trong phòng, cần hỗ trợ bàn ghế, đồ ăn thức uống',
            'Có cuộc gọi quan trọng từ nước ngoài, cần internet ổn định, không bị gián đoạn',
            'Lịch trình thay đổi, có thể cần gia hạn thêm 1-2 ngày, sẽ báo trước',
            'Đây là lần đầu đến đây, cần tư vấn về các địa điểm tham quan, ăn uống gần đây',
            'Khách hàng thân thiết, đã đặt nhiều lần, ưu tiên phòng view đẹp, tầng cao',
            'Đặt phòng khẩn cấp do thay đổi kế hoạch, mong được hỗ trợ nhanh chóng',
            'Có thể có thay đổi về số lượng người, sẽ cập nhật sớm nhất có thể',
            'Yêu cầu xuất hóa đơn VAT, thông tin công ty sẽ gửi sau qua email',
            'Cần gửi thông tin chi tiết về phòng, dịch vụ qua email trước khi đến',
            'Khách hàng đã từng gặp vấn đề với phòng trước đó, cần đảm bảo chất lượng tốt',
            'Đặt phòng cho nhóm lớn, cần nhiều phòng gần nhau, có thể liên thông',
        ];

        $roomPricesGrouped = DB::table('room_prices')
            ->select('id', 'room_id')
            ->get()
            ->groupBy('room_id')
            ->map(function ($items) {
                return $items->pluck('id')->toArray();
            })
            ->toArray();

        $bookingsData = [];

        foreach (range(1, 600) as $i) {
            $start = $faker->dateTimeBetween('-1 month', '+1 month');
            $end   = (clone $start)->modify('+' . rand(5, 30) . ' days');

            $selectedNotes = $faker->randomElements($noteTemplates, rand(1, 3));
            $note          = implode('. ', $selectedNotes);

            if (rand(1, 3) === 1) {
                $note .= '. ' . $faker->randomElement([
                    'Mong được hỗ trợ nhiệt tình từ đội ngũ nhân viên',
                    'Rất mong đợi được trải nghiệm dịch vụ tại đây',
                    'Nếu có thể, xin vui lòng liên hệ trước khi đến để xác nhận',
                    'Cảm ơn sự hỗ trợ của quý khách sạn',
                    'Sẽ có thể đánh giá và phản hồi sau khi sử dụng dịch vụ',
                ]);
            }

            $roomId = $faker->randomElement($roomIds);
            $roomPriceIds = $roomPricesGrouped[$roomId] ?? [];

            // Skip this booking if room has no prices
            if (empty($roomPriceIds)) {
                continue;
            }

            $priceId = $faker->randomElement($roomPriceIds);

            // Status: 0=pending, 1=confirmed, 2=cancelled, 3=completed
            $status = $faker->randomElement([0, 1, 2, 3]);

            $bookingsData[] = [
                'user_id'    => $faker->randomElement($userIds),
                'room_id'    => $roomId,
                'price_id'   => $priceId,
                'start_date' => $start->format('Y-m-d'),
                'end_date'   => $end->format('Y-m-d'),
                'status'     => $status,
                'note'       => $note,
                'created_by' => $faker->randomElement($adminPartnerIds),
                'updated_by' => $faker->randomElement($adminPartnerIds),
                'created_at' => Carbon::now()->subDays(rand(2, 40)),
                'updated_at' => Carbon::now()->subDays(rand(2, 40)),
            ];
        }

        collect($bookingsData)->chunk(100)->each(function ($chunk) {
            DB::table('bookings')->insert($chunk->toArray());
        });
    }
}

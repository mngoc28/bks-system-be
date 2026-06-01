<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Concerns\ResolvesBookingPriceId;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class BookingsTableSeeder extends Seeder
{
    use ResolvesBookingPriceId;

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

        if ($adminPartnerIds === []) {
            $adminPartnerIds = [1];
        }

        $userIds = DB::table('users')
            ->where('role', 'user')
            ->where('id', '!=', 1)
            ->pluck('id')
            ->toArray();

        if ($userIds === []) {
            $userIds = range(2, 100);
        }

        $roomIds = DB::table('rooms')->pluck('id')->toArray();

        if ($roomIds === []) {
            $this->command->warn('No rooms found. Please run RoomsTableSeeder first.');

            return;
        }

        $pricesByRoomId = $this->loadRoomPricesIndexedByRoomId();

        if ($pricesByRoomId === []) {
            $this->command->warn('No room_prices found. Please run RoomPricesTableSeeder first.');

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
            'Lưu trú dài hạn cho nhân viên công ty, yêu cầu giá cả hợp lý',
        ];

        $bookingsData = [];

        foreach (range(1, 600) as $i) {
            $start = $faker->dateTimeBetween('-1 month', '+1 month');
            $stayDays = random_int(5, 45);
            $end = (clone $start)->modify('+' . $stayDays . ' days');

            $startDate = $start->format('Y-m-d');
            $endDate = $end->format('Y-m-d');

            $selectedNotes = $faker->randomElements($noteTemplates, random_int(1, 3));
            $note = implode('. ', $selectedNotes);

            if (random_int(1, 3) === 1) {
                $note .= '. ' . $faker->randomElement([
                    'Mong được hỗ trợ nhiệt tình từ đội ngũ nhân viên',
                    'Rất mong đợi được trải nghiệm dịch vụ tại đây',
                    'Nếu có thể, xin vui lòng liên hệ trước khi đến để xác nhận',
                ]);
            }

            $roomId = (int) $faker->randomElement($roomIds);
            $priceId = $this->resolvePriceIdForStay($roomId, $startDate, $endDate, $pricesByRoomId);

            if ($priceId === null) {
                continue;
            }

            $bookingsData[] = [
                'user_id' => $faker->randomElement($userIds),
                'room_id' => $roomId,
                'price_id' => $priceId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $faker->randomElement([0, 1, 2, 3]),
                'note' => $note,
                'created_by' => $faker->randomElement($adminPartnerIds),
                'updated_by' => $faker->randomElement($adminPartnerIds),
                'created_at' => Carbon::now()->subDays(random_int(2, 40)),
                'updated_at' => Carbon::now()->subDays(random_int(2, 40)),
            ];
        }

        collect($bookingsData)->chunk(100)->each(function ($chunk): void {
            DB::table('bookings')->insert($chunk->toArray());
        });
    }
}

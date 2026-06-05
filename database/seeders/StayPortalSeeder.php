<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Concerns\ResolvesBookingPriceId;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class StayPortalSeeder extends Seeder
{
    use ResolvesBookingPriceId;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // 1. Ensure the test user exists and has a rich profile
        $user = User::updateOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Nguyễn Văn User',
                'password' => Hash::make('123456a!'),
                'role' => 'user',
                'is_email_verified' => true,
                'phone' => '0987654321',
                'address' => '123 Đường BKS, Quận Cầu Giấy, Hà Nội',
                'reward_points' => 2500,
                'membership_level' => 'Diamond',
                'status' => '1',
            ]
        );

        $this->command->info("User {$user->email} updated with loyalty data.");

        // 2. Prepare some random data for bookings
        $rooms = Room::all();
        $services = Service::all();
        $adminId = User::where('role', 'admin')->first()?->id ?? 1;

        if ($rooms->isEmpty()) {
            $this->command->error('No rooms found. Please run RoomsTableSeeder first.');
            return;
        }

        $pricesByRoomId = $this->loadRoomPricesIndexedByRoomId();

        if ($pricesByRoomId === []) {
            $this->command->error('No room_prices found. Please run RoomPricesTableSeeder first.');
            return;
        }

        // Cleanup existing stay data for this user to avoid mess
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $bookingIds = Booking::where('user_id', $user->id)->pluck('id');
        DB::table('settlement_line_items')->whereIn('booking_id', $bookingIds)->delete();
        DB::table('contracts')->whereIn('booking_id', $bookingIds)->delete();
        Booking::where('user_id', $user->id)->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 3. Create ONE ACTIVE BOOKING (In Progress) — 30 ngày → ưu tiên gói tháng
        $activeRoom = $rooms->random();
        $activeStart = Carbon::now()->subDays(5)->format('Y-m-d');
        $activeEnd = Carbon::now()->addDays(25)->format('Y-m-d');
        $activePriceId = $this->resolvePriceIdForStay(
            (int) $activeRoom->id,
            $activeStart,
            $activeEnd,
            $pricesByRoomId,
        );

        if ($activePriceId === null) {
            $this->command->error('No price for active stay demo room.');
            return;
        }

        $activeBooking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $activeRoom->id,
            'price_id' => $activePriceId,
            'start_date' => $activeStart,
            'end_date' => $activeEnd,
            'status' => 1, // Confirmed / In Progress
            'note' => 'Yêu cầu phòng yên tĩnh và dọn phòng vào buổi sáng.',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        // Attach Services to Active Booking
        $activeServices = $services->whereIn('price', [0, 50000, 150000])->random(3);
        foreach ($activeServices as $service) {
            DB::table('booking_services')->insert([
                'booking_id' => $activeBooking->id,
                'service_id' => $service->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create Signed Contract for Active Booking
        Contract::create([
            'booking_id' => $activeBooking->id,
            'title' => 'Hợp đồng thuê phòng số #' . $activeBooking->id,
            'content' => 'Nội dung hợp đồng thuê phòng giữa BKS Stay và ông/bà ' . $user->name . '. Các điều khoản bao gồm thời gian lưu trú, quy định sử dụng phòng và trách nhiệm của hai bên...',
            'status' => 1, // Signed
            'type' => 'Rental',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $this->command->info('Active booking and contract created.');

        // 4. Create ONE UPCOMING BOOKING (Pending) — 5 ngày → ưu tiên gói ngày
        $upcomingRoom = $rooms->where('id', '!=', $activeRoom->id)->random();
        $upcomingStart = Carbon::now()->addDays(35)->format('Y-m-d');
        $upcomingEnd = Carbon::now()->addDays(40)->format('Y-m-d');
        $upcomingPriceId = $this->resolvePriceIdForStay(
            (int) $upcomingRoom->id,
            $upcomingStart,
            $upcomingEnd,
            $pricesByRoomId,
        );

        if ($upcomingPriceId === null) {
            $this->command->error('No price for upcoming stay demo room.');
            return;
        }

        $upcomingBooking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $upcomingRoom->id,
            'price_id' => $upcomingPriceId,
            'start_date' => $upcomingStart,
            'end_date' => $upcomingEnd,
            'status' => 0, // Pending
            'note' => 'Kỳ nghỉ gia đình sắp tới.',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        // Create Pending Contract for Upcoming Booking
        Contract::create([
            'booking_id' => $upcomingBooking->id,
            'title' => 'Hợp đồng thuê phòng số #' . $upcomingBooking->id,
            'content' => 'Hợp đồng này đang chờ bạn xem xét và ký để xác nhận kỳ nghỉ sắp tới. Vui lòng kiểm tra kỹ các thông tin về giá thuê và thời gian...',
            'status' => 0, // Pending
            'type' => 'Rental',
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        $this->command->info('Upcoming booking and pending contract created.');

        // 5. Create 5 COMPLETED BOOKINGS (History)
        for ($i = 1; $i <= 5; $i++) {
            $historyRoom = $rooms->random();
            $monthsAgo = $i * 2;
            $historyStart = Carbon::now()->subMonths($monthsAgo)->subDays(10)->format('Y-m-d');
            $historyEnd = Carbon::now()->subMonths($monthsAgo)->subDays(5)->format('Y-m-d');
            $historyPriceId = $this->resolvePriceIdForStay(
                (int) $historyRoom->id,
                $historyStart,
                $historyEnd,
                $pricesByRoomId,
            );

            if ($historyPriceId === null) {
                continue;
            }

            Booking::create([
                'user_id' => $user->id,
                'room_id' => $historyRoom->id,
                'price_id' => $historyPriceId,
                'start_date' => $historyStart,
                'end_date' => $historyEnd,
                'status' => 2, // Completed
                'note' => 'Lịch sử kỳ nghỉ cũ #' . $i,
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'created_at' => Carbon::now()->subMonths($monthsAgo),
            ]);
        }

        $this->command->info('History of 5 bookings created.');
    }
}

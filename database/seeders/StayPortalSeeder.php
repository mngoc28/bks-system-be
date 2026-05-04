<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Contract;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StayPortalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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

        // Cleanup existing stay data for this user to avoid mess
        Booking::where('user_id', $user->id)->delete();

        // 3. Create ONE ACTIVE BOOKING (In Progress)
        $activeRoom = $rooms->random();
        $activeBooking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $activeRoom->id,
            'price_id' => DB::table('room_prices')->where('room_id', $activeRoom->id)->first()?->id ?? 1,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
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

        // 4. Create ONE UPCOMING BOOKING (Pending)
        $upcomingRoom = $rooms->where('id', '!=', $activeRoom->id)->random();
        $upcomingBooking = Booking::create([
            'user_id' => $user->id,
            'room_id' => $upcomingRoom->id,
            'price_id' => DB::table('room_prices')->where('room_id', $upcomingRoom->id)->first()?->id ?? 1,
            'start_date' => Carbon::now()->addDays(35),
            'end_date' => Carbon::now()->addDays(40),
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
            Booking::create([
                'user_id' => $user->id,
                'room_id' => $historyRoom->id,
                'price_id' => DB::table('room_prices')->where('room_id', $historyRoom->id)->first()?->id ?? 1,
                'start_date' => Carbon::now()->subMonths($monthsAgo)->subDays(10),
                'end_date' => Carbon::now()->subMonths($monthsAgo)->subDays(5),
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

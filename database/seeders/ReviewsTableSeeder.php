<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clean old reviews
        Review::truncate();

        // Get all users who can write reviews (role = user)
        $users = User::where('role', 'user')->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }

        if ($users->isEmpty()) {
            $this->command->warn("No users found to author reviews. Please run UsersTableSeeder first.");
            return;
        }

        // Room reviews pools
        $roomComments = [
            "Phòng siêu sạch sẽ, đầy đủ tiện nghi và không gian yên tĩnh tuyệt đối.",
            "Nội thất hiện đại, thông minh, phòng tắm sạch sẽ, nước nóng ổn định.",
            "Không gian rộng rãi, thoáng mát, ban công view cực kỳ thoáng đãng.",
            "Giường ngủ siêu êm, chăn drap thơm tho sạch sẽ, wifi tốc độ cao mượt mà.",
            "Phòng decor xinh xắn, đầy đủ dụng cụ làm bếp cơ bản, cảm giác ấm cúng như ở nhà.",
            "Vị trí đắc địa, giao thông đi lại thuận tiện, phòng cách âm tốt.",
            "Trải nghiệm tuyệt vời, giá cả hợp lý so với chất lượng phòng.",
            "Mọi thứ đều sạch sẽ, gọn gàng và ngăn nắp đúng như mô tả.",
        ];

        // Partner reviews pools
        $partnerComments = [
            "Chủ nhà hỗ trợ vô cùng nhiệt tình, trả lời tin nhắn nhanh và giải quyết vấn đề chu đáo.",
            "Thủ tục nhận phòng nhanh chóng, lễ tân thân thiện và dễ mến.",
            "Host siêu thân thiện, chu đáo chuẩn bị cả nước uống và bản đồ hướng dẫn.",
            "Phục vụ chuyên nghiệp, hỗ trợ nhiệt tình khi mình cần check-out muộn.",
            "Dịch vụ xuất sắc, chủ nhà dễ thương và phản hồi cực kỳ nhanh nhẹn.",
            "Landlord rất văn minh, tôn trọng sự riêng tư và hỗ trợ khách hết mình.",
        ];

        $reviewsData = [];

        // Seed reviews based on bookings to keep relational integrity
        $bookings = Booking::with('room.property')->get();

        foreach ($bookings as $booking) {
            if (!$booking->room) {
                continue;
            }

            $userId = $booking->user_id;
            $roomId = $booking->room_id;
            $partnerId = $booking->room->property->user_id ?? null;

            // Seed room review (80% chance)
            if (rand(0, 100) < 80) {
                $reviewsData[] = [
                    'user_id' => $userId,
                    'booking_id' => $booking->id,
                    'room_id' => $roomId,
                    'partner_id' => null,
                    'rating' => rand(4, 5),
                    'comment' => $roomComments[array_rand($roomComments)],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ];
            }

            // Seed partner review (70% chance)
            if ($partnerId && rand(0, 100) < 70) {
                $reviewsData[] = [
                    'user_id' => $userId,
                    'booking_id' => $booking->id,
                    'room_id' => null,
                    'partner_id' => $partnerId,
                    'rating' => rand(4, 5),
                    'comment' => $partnerComments[array_rand($partnerComments)],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ];
            }
        }

        // Seeding multiple reviews for ALL rooms to increase density
        $rooms = Room::with('property')->get();
        foreach ($rooms as $room) {
            // Seed 3 to 7 reviews per room with distinct authors
            $numRoomReviews = rand(3, 7);
            $roomAuthors = $users->shuffle()->take($numRoomReviews);
            foreach ($roomAuthors as $author) {
                $reviewsData[] = [
                    'user_id' => $author->id,
                    'booking_id' => null,
                    'room_id' => $room->id,
                    'partner_id' => null,
                    'rating' => rand(3, 5), // A natural mix of ratings
                    'comment' => $roomComments[array_rand($roomComments)],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ];
            }
        }

        // Seed 10 to 30 reviews per partner with distinct authors
        $partnerIds = $rooms->pluck('property.user_id')->filter()->unique();
        foreach ($partnerIds as $partnerId) {
            $numPartnerReviews = rand(10, 30);
            $partnerAuthors = $users->shuffle()->take($numPartnerReviews);
            foreach ($partnerAuthors as $author) {
                $reviewsData[] = [
                    'user_id' => $author->id,
                    'booking_id' => null,
                    'room_id' => null,
                    'partner_id' => $partnerId,
                    'rating' => rand(3, 5),
                    'comment' => $partnerComments[array_rand($partnerComments)],
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ];
            }
        }

        // Insert reviews in chunks of 500 to avoid query size/timeout issues
        collect($reviewsData)->chunk(500)->each(function ($chunk) {
            DB::table('reviews')->insert($chunk->toArray());
        });

        $this->command->info("Reviews table seeded successfully!");
    }
}

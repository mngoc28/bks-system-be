<?php

namespace Database\Seeders;

use App\Models\ChatbotAnswer;
use App\Models\ChatbotQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatbotSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('chatbot_answers')->truncate();
        DB::table('chatbot_questions')->truncate();

        $timestamp = now();

        // Define realistic questions
        $questionsData = [
            1 => [
                'content' => 'Chào bạn! Tôi là trợ lý ảo BKS. Tôi có thể giúp gì cho bạn hôm nay?',
                'is_start_node' => 1,
                'answers' => [
                    ['content' => 'Tìm phòng trống', 'next' => 2],
                    ['content' => 'Xem bảng giá dịch vụ', 'next' => 3],
                    ['content' => 'Chính sách hoàn/hủy', 'next' => 4],
                    ['content' => 'Liên hệ nhân viên', 'next' => 5],
                ]
            ],
            2 => [
                'content' => 'Bạn muốn tìm phòng ở khu vực nào?',
                'is_start_node' => 0,
                'answers' => [
                    ['content' => 'Hà Nội', 'next' => 6],
                    ['content' => 'TP. Hồ Chí Minh', 'next' => 7],
                    ['content' => 'Đà Nẵng', 'next' => 8],
                ]
            ],
            3 => [
                'content' => 'Bảng giá dịch vụ cơ bản: Điện: 3.500đ/kwh, Nước: 20.000đ/khối, Phí dịch vụ: 150.000đ/tháng. Bạn có thắc mắc gì khác không?',
                'is_start_node' => 0,
                'answers' => [
                    ['content' => 'Quay lại menu chính', 'next' => 1],
                    ['content' => 'Kết thúc hỗ trợ', 'next' => null, 'is_final' => 1],
                ]
            ],
            4 => [
                'content' => 'Chính sách hoàn/hủy: Hủy trước 7 ngày hoàn 100%, trước 3 ngày hoàn 50%, sau đó không hoàn lại tiền cọc. Bạn có muốn tiếp tục không?',
                'is_start_node' => 0,
                'answers' => [
                    ['content' => 'Quay lại menu chính', 'next' => 1],
                    ['content' => 'Cần gặp nhân viên tư vấn', 'next' => 5],
                ]
            ],
            5 => [
                'content' => 'Bạn vui lòng liên hệ Hotline: 1900 1234 hoặc để lại số điện thoại để nhân viên gọi lại tư vấn nhé!',
                'is_start_node' => 0,
                'answers' => [
                    ['content' => 'Để lại số điện thoại', 'next' => null, 'is_final' => 1],
                    ['content' => 'Quay lại', 'next' => 1],
                ]
            ],
            6 => [
                'content' => 'Hiện tại Hà Nội đang có sẵn phòng tại các quận: Thanh Xuân, Cầu Giấy và Đống Đa. Bạn muốn xem chi tiết khu vực nào?',
                'is_start_node' => 0,
                'answers' => [
                    ['content' => 'Quận Thanh Xuân', 'next' => null, 'is_final' => 1],
                    ['content' => 'Quận Cầu Giấy', 'next' => null, 'is_final' => 1],
                    ['content' => 'Quay lại', 'next' => 2],
                ]
            ],
            7 => [
                'content' => 'TP. HCM đang có sẵn căn hộ tại Quận 1, Quận 3 và Quận Bình Thạnh. Bạn muốn tham khảo khu vực nào?',
                'is_start_node' => 0,
                'answers' => [
                    ['content' => 'Quận 1', 'next' => null, 'is_final' => 1],
                    ['content' => 'Quận Bình Thạnh', 'next' => null, 'is_final' => 1],
                    ['content' => 'Quay lại', 'next' => 2],
                ]
            ],
            8 => [
                'content' => 'Đà Nẵng hiện có sẵn các căn hộ view biển tại Quận Hải Châu và Ngũ Hành Sơn. Bạn muốn xem chứ?',
                'is_start_node' => 0,
                'answers' => [
                    ['content' => 'Quận Hải Châu', 'next' => null, 'is_final' => 1],
                    ['content' => 'Quay lại', 'next' => 2],
                ]
            ]
        ];

        foreach ($questionsData as $id => $data) {
            DB::table('chatbot_questions')->insert([
                'id' => $id,
                'content' => $data['content'],
                'type' => 0,
                'position_x' => rand(100, 800),
                'position_y' => rand(100, 600),
                'is_start_node' => $data['is_start_node'],
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            foreach ($data['answers'] as $a) {
                DB::table('chatbot_answers')->insert([
                    'question_id' => $id,
                    'content' => $a['content'],
                    'next_question_id' => $a['next'] ?? null,
                    'is_final' => $a['is_final'] ?? 0,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }
    }
}

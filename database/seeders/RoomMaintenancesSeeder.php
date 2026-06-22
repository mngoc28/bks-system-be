<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomMaintenancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [];
        $maintenanceTasks = [
            ['title' => 'Bảo dưỡng điều hòa', 'description' => 'Vệ sinh lưới lọc, kiểm tra gas và hệ thống làm lạnh định kỳ.'],
            ['title' => 'Sửa chữa vòi nước rò rỉ', 'description' => 'Thay thế gioăng cao su và linh kiện vòi nước bị hỏng.'],
            ['title' => 'Kiểm tra hệ thống điện', 'description' => 'Đo lường nguồn điện, kiểm tra Aptomat và các ổ cắm trong phòng.'],
            ['title' => 'Thay bóng đèn LED', 'description' => 'Thay thế các bóng đèn đã quá tuổi thọ hoặc bị cháy.'],
            ['title' => 'Sơn sửa tường', 'description' => 'Xử lý các vết bong tróc, thấm dột và sơn lại màu đồng bộ.'],
            ['title' => 'Vệ sinh sofa/rèm cửa', 'description' => 'Giặt khô và khử khuẩn các đồ nội thất bằng vải.'],
            ['title' => 'Thông nghẹt thoát sàn', 'description' => 'Xử lý tắc nghẽn đường ống thoát nước trong nhà vệ sinh.'],
            ['title' => 'Thay thế khóa cửa', 'description' => 'Cài đặt lại khóa thông minh hoặc thay thế lõi khóa cơ.'],
            ['title' => 'Kiểm tra hệ thống PCCC', 'description' => 'Đảm bảo đầu báo khói và vòi phun hoạt động tốt.'],
            ['title' => 'Bảo trì tủ lạnh', 'description' => 'Vệ sinh dàn nóng và kiểm tra nhiệt độ ngăn đông.'],
        ];

        for ($i = 1; $i <= 20; $i++) {
            $start = Carbon::now()->addDays(rand(-10, 20))->setTime(rand(8, 16), 0);
            $end = (clone $start)->addHours(rand(2, 6));
            $task = $maintenanceTasks[array_rand($maintenanceTasks)];

            $status = collect(['planned', 'in_progress', 'completed', 'cancelled'])->random();

            $records[] = [
                'room_id' => (($i - 1) % 10) + 1,
                'property_id' => (($i - 1) % 5) + 1,
                'title' => $task['title'] . ' #' . $i,
                'description' => $task['description'],
                'maintenance_type' => rand(0, 1) ? 'scheduled' : 'emergency',
                'start_time' => $start,
                'end_time' => $end,
                'status' => $status,
                'room_block_id' => null,
                'block_calendar' => true,
                'source' => 'partner',
                'cancellation_reason' => $status === 'cancelled' ? 'Hủy theo kế hoạch vận hành (seed)' : null,
                'started_at' => in_array($status, ['in_progress', 'completed', 'cancelled'], true) ? $start : null,
                'completed_at' => $status === 'completed' ? $end : null,
                'cancelled_at' => $status === 'cancelled' ? $end : null,
                'created_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        DB::table('room_maintenances')->insert($records);
    }
}

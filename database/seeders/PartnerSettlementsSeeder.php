<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\PartnerSettlementPeriod;
use App\Models\SettlementAdjustment;
use App\Models\SettlementLineItem;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PartnerSettlementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        // 1. Lấy hoặc kiểm tra Admin và Partners
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin ? $admin->id : 1;

        $targetPartner = User::where('email', 'partner@gmail.com')->first();
        if (!$targetPartner) {
            $this->command->warn('Không tìm thấy partner@gmail.com. Vui lòng chạy UsersTableSeeder trước.');
            return;
        }
        $targetPartnerId = $targetPartner->id;

        // Lấy thêm một số partner khác để seed đa dạng dữ liệu
        $otherPartners = User::where('role', 'partner')
            ->where('id', '!=', $targetPartnerId)
            ->limit(3)
            ->get();

        // 2. Đồng bộ hóa quyền sở hữu Property & Room cho target partner
        // Lấy 5 properties đầu tiên và gán cho partner 2 sở hữu
        $properties = DB::table('properties')->limit(5)->pluck('id')->toArray();
        if (empty($properties)) {
            $this->command->warn('Không tìm thấy properties nào. Vui lòng chạy PropertiesTableSeeder trước.');
            return;
        }

        DB::table('properties')
            ->whereIn('id', $properties)
            ->update(['user_id' => $targetPartnerId]);

        // Lấy danh sách rooms thuộc 5 properties này
        $rooms = DB::table('rooms')
            ->whereIn('property_id', $properties)
            ->pluck('id')
            ->toArray();

        if (empty($rooms)) {
            $this->command->warn('Không tìm thấy rooms nào cho các properties của target partner.');
            return;
        }

        // Cập nhật ngẫu nhiên 120 bookings hiện có sang các rooms này để ta có đủ bookings chốt đối soát
        $bookings = DB::table('bookings')->limit(120)->pluck('id')->toArray();
        if (empty($bookings)) {
            $this->command->warn('Không tìm thấy bookings nào. Vui lòng chạy BookingsTableSeeder trước.');
            return;
        }

        foreach ($bookings as $bookingId) {
            DB::table('bookings')
                ->where('id', $bookingId)
                ->update([
                    'room_id' => $faker->randomElement($rooms),
                    'status' => 3, // Completed status
                    'stay_status' => 'checked_out',
                    'updated_at' => Carbon::now()->subDays(rand(1, 45)),
                ]);
        }

        // Dọn dẹp dữ liệu đối soát cũ trước khi seed
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('settlement_adjustments')->truncate();
        DB::table('settlement_line_items')->truncate();
        DB::table('partner_settlement_periods')->truncate();
        DB::table('bookings')->update(['settlement_period_id' => null, 'payment_collected_at' => null]);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // 3. Định nghĩa các mốc thời gian an toàn tránh lỗi tràn ngày cuối tháng (Date Overflow)
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->startOfMonth()->subMonth();
        $twoMonthsAgo = Carbon::now()->startOfMonth()->subMonths(2);

        $periodsToSeed = [
            [
                'status' => PartnerSettlementPeriod::STATUS_CLOSED,
                'start' => $twoMonthsAgo->copy()->day(1),
                'end' => $twoMonthsAgo->copy()->day(15),
                'issue' => $twoMonthsAgo->copy()->day(20),
                'ref' => 'FT' . $twoMonthsAgo->format('ymd') . '00231',
                'confirm_note' => 'Đã xác nhận khớp số liệu tài chính qua chuyển khoản VCB.',
            ],
            [
                'status' => PartnerSettlementPeriod::STATUS_PAID,
                'start' => $twoMonthsAgo->copy()->day(16),
                'end' => $twoMonthsAgo->copy()->endOfMonth(),
                'issue' => $lastMonth->copy()->day(5),
                'ref' => 'FT' . $lastMonth->format('ymd') . '11928',
                'confirm_note' => 'Thanh toán trễ hạn 2 ngày nhưng đã nhận đủ tiền.',
            ],
            [
                'status' => PartnerSettlementPeriod::STATUS_ISSUED,
                'start' => $lastMonth->copy()->day(1),
                'end' => $lastMonth->copy()->day(15),
                'issue' => $lastMonth->copy()->day(20),
                'ref' => null,
                'confirm_note' => null,
            ],
            [
                'status' => PartnerSettlementPeriod::STATUS_DISPUTED,
                'start' => $lastMonth->copy()->day(16),
                'end' => $lastMonth->copy()->endOfMonth(),
                'issue' => $thisMonth->copy()->day(5),
                'ref' => null,
                'confirm_note' => null,
                'dispute_reason' => 'Đối tác phản hồi sai lệch tiền dịch vụ giặt là ở mã BK-10928 (tính thừa 150,000đ). Cần kiểm tra lại log dịch vụ.',
            ],
            [
                'status' => PartnerSettlementPeriod::STATUS_DRAFT,
                'start' => $thisMonth->copy()->day(1),
                'end' => $thisMonth->copy()->day(15),
                'issue' => $thisMonth->copy()->day(20),
                'ref' => null,
                'confirm_note' => null,
            ]
        ];

        // Lấy danh sách bookings có sẵn để gán vào các kỳ
        $usableBookings = Booking::whereHas('room.property', function ($query) use ($targetPartnerId) {
            $query->where('user_id', $targetPartnerId);
        })->get();

        $bookingIndex = 0;
        $commissionRate = 0.05; // 5%

        foreach ($periodsToSeed as $pIdx => $periodConf) {
            // Tạo kỳ đối soát
            $period = PartnerSettlementPeriod::create([
                'partner_id' => $targetPartnerId,
                'period_start' => $periodConf['start']->toDateString(),
                'period_end' => $periodConf['end']->toDateString(),
                'issue_date' => $periodConf['issue']->toDateString(),
                'total_gmv' => 0.0,
                'total_commission' => 0.0,
                'commission_rate' => $commissionRate,
                'status' => $periodConf['status'],
                'payment_reference' => $periodConf['ref'],
                'confirmed_by' => $periodConf['ref'] ? $adminId : null,
                'issued_at' => $periodConf['status'] !== PartnerSettlementPeriod::STATUS_DRAFT ? $periodConf['issue']->copy()->addHours(8) : null,
                'paid_at' => $periodConf['status'] === PartnerSettlementPeriod::STATUS_PAID || $periodConf['status'] === PartnerSettlementPeriod::STATUS_CLOSED ? $periodConf['issue']->copy()->addDays(3)->addHours(14) : null,
                'note' => $periodConf['confirm_note'] ? '[Xác nhận thanh toán] ' . $periodConf['confirm_note'] : null,
            ]);

            if (isset($periodConf['dispute_reason'])) {
                $period->update([
                    'note' => "[Khiếu nại đối tác] " . $periodConf['dispute_reason']
                ]);
            }

            // Gán 5 - 8 bookings cho mỗi kỳ
            $bookingsCount = rand(5, 8);
            $totalGmv = 0.0;
            $totalCommission = 0.0;

            for ($j = 0; $j < $bookingsCount; $j++) {
                if ($bookingIndex >= $usableBookings->count()) {
                    break;
                }

                $booking = $usableBookings->get($bookingIndex);
                $bookingIndex++;

                // Tính toán ngẫu nhiên GMV cho line item
                $roomGmv = rand(1500, 8000) * 1000;
                $servicesGmv = rand(0, 5) === 0 ? rand(100, 800) * 1000 : 0.0;
                $bookingGmv = $roomGmv + $servicesGmv;
                $bookingCommission = round($bookingGmv * $commissionRate, 2);

                // Tạo Line Item
                SettlementLineItem::create([
                    'settlement_period_id' => $period->id,
                    'booking_id' => $booking->id,
                    'booking_code' => 'BK-' . strtoupper(Str::random(8)),
                    'checkout_date' => $periodConf['end']->toDateString(),
                    'room_gmv' => $roomGmv,
                    'services_gmv' => $servicesGmv,
                    'total_gmv' => $bookingGmv,
                    'commission_amount' => $bookingCommission,
                    'snapshot_status' => 2, // Completed
                ]);

                // Cập nhật booking
                $booking->update([
                    'settlement_period_id' => $period->id,
                    'payment_collected_at' => $periodConf['end']->copy()->addHours(12),
                ]);

                $totalGmv += $bookingGmv;
                $totalCommission += $bookingCommission;
            }

            // Cập nhật tổng số tiền cho kỳ
            $period->update([
                'total_gmv' => $totalGmv,
                'total_commission' => $totalCommission,
            ]);

            // Thêm adjustment ngẫu nhiên cho kỳ Đã đóng hoặc Đang khiếu nại để làm giàu dữ liệu
            if ($periodConf['status'] === PartnerSettlementPeriod::STATUS_CLOSED) {
                SettlementAdjustment::create([
                    'settlement_period_id' => $period->id,
                    'amount' => -150000.0,
                    'reason' => 'Giảm trừ hoa hồng: Hỗ trợ đối tác sự cố rò rỉ nước phòng 202 ngày 12/04.',
                    'created_by' => $adminId,
                ]);
            }

            if ($periodConf['status'] === PartnerSettlementPeriod::STATUS_DISPUTED) {
                SettlementAdjustment::create([
                    'settlement_period_id' => $period->id,
                    'amount' => 50000.0,
                    'reason' => 'Điều chỉnh tăng: Cộng thêm phí bù chênh lệch dịch vụ giặt là tháng trước.',
                    'created_by' => $adminId,
                ]);
            }
        }

        // 4. Seed thêm một vài kỳ đối soát cho các Partner khác
        foreach ($otherPartners as $op) {
            // Đảm bảo partner khác này cũng có property & rooms & bookings để không bị lỗi integrity
            $opProperties = DB::table('properties')->where('user_id', $op->id)->pluck('id')->toArray();
            if (empty($opProperties)) {
                // Chuyển 2 properties ngẫu nhiên sang cho partner này
                $randomProps = DB::table('properties')->where('user_id', '!=', $op->id)->inRandomOrder()->limit(2)->pluck('id')->toArray();
                if (!empty($randomProps)) {
                    DB::table('properties')->whereIn('id', $randomProps)->update(['user_id' => $op->id]);
                    $opProperties = $randomProps;
                }
            }

            $opRooms = DB::table('rooms')->whereIn('property_id', $opProperties)->pluck('id')->toArray();
            if (!empty($opRooms)) {
                // Tạo 1 kỳ đối soát đã phát hành cho partner này
                $opPeriod = PartnerSettlementPeriod::create([
                    'partner_id' => $op->id,
                    'period_start' => $lastMonth->copy()->day(1)->toDateString(),
                    'period_end' => $lastMonth->copy()->day(15)->toDateString(),
                    'issue_date' => $lastMonth->copy()->day(20)->toDateString(),
                    'total_gmv' => 15000000.0,
                    'total_commission' => 750000.0,
                    'commission_rate' => 0.05,
                    'status' => PartnerSettlementPeriod::STATUS_ISSUED,
                    'issued_at' => $lastMonth->copy()->day(20)->addHours(9),
                ]);

                // Tạo 3 line items
                for ($k = 0; $k < 3; $k++) {
                    $opBooking = Booking::create([
                        'user_id' => 3, // Khách hàng ngẫu nhiên
                        'room_id' => $faker->randomElement($opRooms),
                        'price_id' => 1,
                        'start_date' => $lastMonth->copy()->day(1),
                        'end_date' => $lastMonth->copy()->day(5),
                        'status' => 3, // Completed status
                        'settlement_period_id' => $opPeriod->id,
                        'payment_collected_at' => $lastMonth->copy()->day(5)->addHours(10),
                    ]);

                    SettlementLineItem::create([
                        'settlement_period_id' => $opPeriod->id,
                        'booking_id' => $opBooking->id,
                        'booking_code' => 'BK-' . strtoupper(Str::random(8)),
                        'checkout_date' => $lastMonth->copy()->day(5)->toDateString(),
                        'room_gmv' => 5000000.0,
                        'services_gmv' => 0.0,
                        'total_gmv' => 5000000.0,
                        'commission_amount' => 250000.0,
                        'snapshot_status' => 2,
                    ]);
                }
            }
        }

        $this->command->info('Seed dữ liệu đối soát đối tác thành công!');
    }
}

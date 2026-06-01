<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\PartnerSettlementPeriod;
use App\Models\SettlementLineItem;
use App\Services\BookingStayAmountCalculator;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Job tự động quét các đơn đặt phòng đã check-out và completed để gom tạo kỳ đối soát nháp (Draft).
 */
class GenerateSettlementPeriodsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Ngày chạy job (dùng để xác định biên kỳ đối soát).
     *
     * @var \Carbon\Carbon
     */
    protected Carbon $runDate;

    /**
     * Create a new job instance.
     *
     * @param \Carbon\Carbon|null $runDate
     */
    public function __construct(?Carbon $runDate = null)
    {
        $this->runDate = $runDate ?? Carbon::now();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('GenerateSettlementPeriodsJob: Bắt đầu quét đơn đặt phòng chốt kỳ đối soát.', [
            'run_date' => $this->runDate->toDateTimeString()
        ]);

        // Xác định biên kỳ chốt và ngày phát hành
        $dates = $this->calculatePeriodDates($this->runDate);
        $periodStart = $dates['start'];
        $periodEnd = $dates['end'];
        $issueDate = $dates['issue_date'];

        Log::info('GenerateSettlementPeriodsJob: Biên kỳ chốt đối soát xác định.', [
            'period_start' => $periodStart->toDateString(),
            'period_end'   => $periodEnd->toDateString(),
            'issue_date'   => $issueDate->toDateString(),
        ]);

        // Lấy tất cả bookings COMPLETED + checked_out OR CANCELLED + forfeited chưa được gán kỳ đối soát
        // và có thời gian cập nhật nằm trong biên kỳ
        $bookings = Booking::query()
            ->with(['room.property', 'services', 'price'])
            ->whereNull('settlement_period_id')
            ->whereBetween('updated_at', [
                $periodStart->copy()->startOfDay(),
                $periodEnd->copy()->endOfDay()
            ])
            ->where(static function ($query): void {
                $query->where(static function ($q): void {
                    $q->where('status', BookingStatus::COMPLETED->value)
                      ->where('stay_status', 'checked_out');
                })->orWhere(static function ($q): void {
                    $q->where('status', BookingStatus::CANCELLED->value)
                      ->where('deposit_status', 'forfeited');
                });
            })
            ->get();

        if ($bookings->isEmpty()) {
            Log::info('GenerateSettlementPeriodsJob: Không tìm thấy đơn đặt phòng nào cần chốt trong kỳ này.');
            return;
        }

        Log::info(sprintf('GenerateSettlementPeriodsJob: Tìm thấy %d đơn đặt phòng cần chốt đối soát.', $bookings->count()));

        // Gom nhóm bookings theo Partner ID (chủ của Property chứa Room được book)
        $groupedBookings = $bookings->groupBy(static function (Booking $booking) {
            return $booking->room?->property?->user_id;
        });

        $commissionRate = (float) config('billing.commission_rate', 0.05);

        foreach ($groupedBookings as $partnerId => $partnerBookings) {
            if (!$partnerId) {
                Log::warning('GenerateSettlementPeriodsJob: Phát hiện đơn đặt phòng không có Partner ID.', [
                    'booking_ids' => $partnerBookings->pluck('id')->all()
                ]);
                continue;
            }

            $this->processPartnerSettlement(
                (int) $partnerId,
                $partnerBookings,
                $periodStart,
                $periodEnd,
                $issueDate,
                $commissionRate
            );
        }

        Log::info('GenerateSettlementPeriodsJob: Hoàn thành quét đơn chốt kỳ đối soát.');
    }

    /**
     * Xử lý tạo hoặc cập nhật kỳ đối soát nháp cho từng đối tác.
     */
    protected function processPartnerSettlement(
        int $partnerId,
        $bookings,
        Carbon $periodStart,
        Carbon $periodEnd,
        Carbon $issueDate,
        float $commissionRate
    ): void {
        DB::transaction(function () use ($partnerId, $bookings, $periodStart, $periodEnd, $issueDate, $commissionRate) {
            // Tìm kỳ đối soát trùng biên ngày của đối tác này
            $period = PartnerSettlementPeriod::query()
                ->where('partner_id', $partnerId)
                ->where('period_start', $periodStart->toDateString())
                ->where('period_end', $periodEnd->toDateString())
                ->first();

            if ($period) {
                // Nếu kỳ đã phát hành hoặc thanh toán/đóng thì không can thiệp để tránh sai lệch kế toán
                if ($period->status !== PartnerSettlementPeriod::STATUS_DRAFT) {
                    Log::warning("GenerateSettlementPeriodsJob: Kỳ đối soát của đối tác #{$partnerId} đã được chốt và ở trạng thái [{$period->status}]. Bỏ qua cập nhật.", [
                        'period_id' => $period->id
                    ]);
                    return;
                }
            } else {
                // Tạo mới kỳ đối soát nháp
                $period = PartnerSettlementPeriod::create([
                    'partner_id'       => $partnerId,
                    'period_start'     => $periodStart->toDateString(),
                    'period_end'       => $periodEnd->toDateString(),
                    'issue_date'       => $issueDate->toDateString(),
                    'total_gmv'        => 0.0,
                    'total_commission' => 0.0,
                    'commission_rate'  => $commissionRate,
                    'status'           => PartnerSettlementPeriod::STATUS_DRAFT,
                ]);
            }

            $totalGmv = (float) $period->total_gmv;
            $totalCommission = (float) $period->total_commission;

            foreach ($bookings as $booking) {
                if ((int) $booking->status === BookingStatus::CANCELLED->value) {
                    // For forfeited cancellation, GMV is the deposit amount
                    $roomGmv = (float) ($booking->deposit_amount ?? 0.0);
                    $servicesGmv = 0.0;
                } else {
                    // Tính toán GMV phòng và GMV dịch vụ bằng Helper
                    $roomGmv = BookingStayAmountCalculator::computeRoomStayTotalForBooking($booking);
                    $servicesGmv = BookingStayAmountCalculator::computeServicesTotalForBooking($booking);
                }
                $bookingGmv = round($roomGmv + $servicesGmv, 2);
                $bookingCommission = round($bookingGmv * $commissionRate, 2);

                // Tạo dòng bảng kê chi tiết SettlementLineItem
                SettlementLineItem::create([
                    'settlement_period_id' => $period->id,
                    'booking_id'           => $booking->id,
                    'booking_code'         => $booking->booking_code ?? '',
                    'checkout_date'        => Carbon::parse($booking->updated_at)->toDateString(),
                    'room_gmv'             => $roomGmv,
                    'services_gmv'         => $servicesGmv,
                    'total_gmv'            => $bookingGmv,
                    'commission_amount'    => $bookingCommission,
                    'snapshot_status'      => $booking->status,
                ]);

                // Liên kết Booking với Kỳ đối soát
                $booking->update([
                    'settlement_period_id' => $period->id
                ]);

                // Cộng lũy kế
                $totalGmv += $bookingGmv;
                $totalCommission += $bookingCommission;
            }

            // Cập nhật lại tổng GMV và Hoa hồng cho Kỳ đối soát nháp
            $period->update([
                'total_gmv'        => round($totalGmv, 2),
                'total_commission' => round($totalCommission, 2),
            ]);

            Log::info("GenerateSettlementPeriodsJob: Đã cập nhật kỳ đối soát Draft #{$period->id} cho đối tác #{$partnerId}.", [
                'total_gmv' => $totalGmv,
                'total_commission' => $totalCommission
            ]);
        });
    }

    /**
     * Tính toán biên ngày của kỳ đối soát dựa trên ngày chạy job.
     *
     * @param \Carbon\Carbon $runDate
     * @return array{start: \Carbon\Carbon, end: \Carbon\Carbon, issue_date: \Carbon\Carbon}
     */
    public function calculatePeriodDates(Carbon $runDate): array
    {
        $day = $runDate->day;

        if ($day < 16) {
            // Chốt cho kỳ từ ngày 16 -> ngày cuối cùng của tháng trước
            $start = $runDate->copy()->subMonth()->day(16)->startOfDay();
            $end = $runDate->copy()->subMonth()->endOfMonth()->endOfDay();
            $issueDate = $runDate->copy()->day(5)->startOfDay();
        } else {
            // Chốt cho kỳ từ ngày 1 -> 15 của tháng hiện tại
            $start = $runDate->copy()->day(1)->startOfDay();
            $end = $runDate->copy()->day(15)->endOfDay();
            $issueDate = $runDate->copy()->day(20)->startOfDay();
        }

        return [
            'start'      => $start,
            'end'        => $end,
            'issue_date' => $issueDate,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\SettlementLineItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service tổng hợp báo cáo doanh thu và hoa hồng nền tảng.
 */
class RevenueReportingService
{
    /**
     * Lấy dữ liệu doanh thu GMV và hoa hồng theo ngày trong khoảng thời gian.
     * Phục vụ vẽ biểu đồ Dashboard.
     *
     * @param string $startDate Y-m-d
     * @param string $endDate Y-m-d
     * @return \Illuminate\Support\Collection
     */
    public function getRevenueDailyReport(string $startDate, string $endDate): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Lấy dữ liệu từ các đơn đã đối soát (truy vấn trực tiếp qua line items để tối ưu tốc độ)
        $settledReport = SettlementLineItem::query()
            ->select([
                DB::raw('DATE(checkout_date) as date'),
                DB::raw('SUM(total_gmv) as total_gmv'),
                DB::raw('SUM(commission_amount) as total_commission'),
            ])
            ->whereBetween('checkout_date', [$start->toDateString(), $end->toDateString()])
            ->groupByRaw('DATE(checkout_date)')
            ->get()
            ->keyBy('date');

        // 2. Lấy dữ liệu từ các đơn completed + checked_out chưa đối soát
        $unsettledBookings = Booking::query()
            ->with(['price', 'services'])
            ->where('status', BookingStatus::COMPLETED->value)
            ->where('stay_status', 'checked_out')
            ->whereNull('settlement_period_id')
            ->whereBetween('updated_at', [$start, $end])
            ->get();

        $commissionRate = (float) config('billing.commission_rate', 0.05);
        $unsettledReport = [];

        foreach ($unsettledBookings as $booking) {
            $date = Carbon::parse($booking->updated_at)->toDateString();
            $roomGmv = BookingStayAmountCalculator::computeRoomStayTotalForBooking($booking);
            $servicesGmv = BookingStayAmountCalculator::computeServicesTotalForBooking($booking);
            $bookingGmv = round($roomGmv + $servicesGmv, 2);
            $bookingCommission = round($bookingGmv * $commissionRate, 2);

            if (!isset($unsettledReport[$date])) {
                $unsettledReport[$date] = [
                    'date'             => $date,
                    'total_gmv'        => 0.0,
                    'total_commission' => 0.0,
                ];
            }

            $unsettledReport[$date]['total_gmv'] += $bookingGmv;
            $unsettledReport[$date]['total_commission'] += $bookingCommission;
        }

        // 3. Hợp nhất hai nguồn dữ liệu
        $mergedReport = collect();
        $current = $start->copy();

        while ($current->lessThanOrEqualTo($end)) {
            $dateStr = $current->toDateString();
            $gmv = 0.0;
            $commission = 0.0;

            if ($settledReport->has($dateStr)) {
                $gmv += (float) $settledReport->get($dateStr)->total_gmv;
                $commission += (float) $settledReport->get($dateStr)->getAttribute('total_commission');
            }

            if (isset($unsettledReport[$dateStr])) {
                $gmv += (float) $unsettledReport[$dateStr]['total_gmv'];
                $commission += (float) $unsettledReport[$dateStr]['total_commission'];
            }

            $mergedReport->push([
                'date'             => $dateStr,
                'total_gmv'        => round($gmv, 2),
                'total_commission' => round($commission, 2),
            ]);

            $current->addDay();
        }

        return $mergedReport;
    }

    /**
     * Lấy báo cáo doanh thu GMV và hoa hồng theo tháng.
     *
     * @param string $year YYYY
     * @return \Illuminate\Support\Collection
     */
    public function getRevenueMonthlyReport(string $year): Collection
    {
        $yearInt = (int) $year;
        $start = Carbon::create($yearInt, 1, 1)->startOfYear();
        $end = Carbon::create($yearInt, 12, 31)->endOfYear();

        $dailyData = $this->getRevenueDailyReport($start->toDateString(), $end->toDateString());

        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = sprintf('%04d-%02d', $yearInt, $m);
            $monthlyData[$monthKey] = [
                'month'            => $monthKey,
                'total_gmv'        => 0.0,
                'total_commission' => 0.0,
            ];
        }

        foreach ($dailyData as $row) {
            $month = Carbon::parse($row['date'])->format('Y-m');
            if (isset($monthlyData[$month])) {
                $monthlyData[$month]['total_gmv'] += $row['total_gmv'];
                $monthlyData[$month]['total_commission'] += $row['total_commission'];
            }
        }

        // Làm tròn số liệu
        return collect(array_values($monthlyData))->map(static function (array $item) {
            return [
                'month'            => $item['month'],
                'total_gmv'        => round($item['total_gmv'], 2),
                'total_commission' => round($item['total_commission'], 2),
            ];
        });
    }

    /**
     * Lấy tổng quan các chỉ số tài chính Admin Dashboard.
     *
     * @return array{
     *     total_gmv: float,
     *     total_commission: float,
     *     pending_commission: float,
     *     paid_commission: float
     * }
     */
    public function getAdminRevenueSummary(): array
    {
        // 1. Tổng GMV và hoa hồng từ tất cả các đơn completed + checked_out
        $allBookings = Booking::query()
            ->with(['price', 'services'])
            ->where('status', BookingStatus::COMPLETED->value)
            ->where('stay_status', 'checked_out')
            ->get();

        $commissionRate = (float) config('billing.commission_rate', 0.05);
        $totalGmv = 0.0;
        $totalCommission = 0.0;

        foreach ($allBookings as $booking) {
            if ($booking->settlement_period_id) {
                // Đơn đã chốt thì lấy từ line item để chính xác theo snapshot
                $lineItem = SettlementLineItem::query()
                    ->where('booking_id', $booking->id)
                    ->first();
                if ($lineItem) {
                    $totalGmv += (float) $lineItem->total_gmv;
                    $totalCommission += (float) $lineItem->commission_amount;
                    continue;
                }
            }

            // Tính tạm tính đối với đơn chưa chốt
            $roomGmv = BookingStayAmountCalculator::computeRoomStayTotalForBooking($booking);
            $servicesGmv = BookingStayAmountCalculator::computeServicesTotalForBooking($booking);
            $bookingGmv = $roomGmv + $servicesGmv;
            $totalGmv += $bookingGmv;
            $totalCommission += ($bookingGmv * $commissionRate);
        }

        // 2. Phân loại hoa hồng theo trạng thái thanh toán từ bảng Settlement periods
        // Hoa hồng đã thu (Paid & Closed)
        $paidCommission = (float) DB::table('partner_settlement_periods')
            ->whereIn('status', ['paid', 'closed'])
            ->sum('total_commission');

        // Ghi nhận thêm tổng điều chỉnh của các kỳ đã thanh toán/đóng
        $paidAdjustments = (float) DB::table('settlement_adjustments')
            ->join(
                'partner_settlement_periods',
                'settlement_adjustments.settlement_period_id',
                '=',
                'partner_settlement_periods.id'
            )
            ->whereIn('partner_settlement_periods.status', ['paid', 'closed'])
            ->sum('settlement_adjustments.amount');

        $paidCommissionTotal = round($paidCommission + $paidAdjustments, 2);

        // Hoa hồng đang chờ thu (Issued, Disputed)
        $pendingCommission = (float) DB::table('partner_settlement_periods')
            ->whereIn('status', ['issued', 'disputed'])
            ->sum('total_commission');

        $pendingAdjustments = (float) DB::table('settlement_adjustments')
            ->join(
                'partner_settlement_periods',
                'settlement_adjustments.settlement_period_id',
                '=',
                'partner_settlement_periods.id'
            )
            ->whereIn('partner_settlement_periods.status', ['issued', 'disputed'])
            ->sum('settlement_adjustments.amount');

        $pendingCommissionTotal = round($pendingCommission + $pendingAdjustments, 2);

        return [
            'total_gmv'          => round($totalGmv, 2),
            'total_commission'   => round($totalCommission, 2),
            'pending_commission' => $pendingCommissionTotal,
            'paid_commission'    => $paidCommissionTotal,
        ];
    }
}

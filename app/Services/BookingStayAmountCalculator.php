<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\RoomPrice;
use Carbon\Carbon;

/**
 * Tính tiền lưu trú theo đơn vị gói giá (day / week / month / year) trên room_prices.
 */
final class BookingStayAmountCalculator
{
    public const DAYS_PER_MONTH = 30;

    public const DAYS_PER_WEEK = 7;

    public const DAYS_PER_YEAR = 365;

    /** Số đêm lưu trú (checkout exclusive) — REQ-STAY-002. */
    public static function countStayNights(string|\DateTimeInterface $start, string|\DateTimeInterface $end): int
    {
        return StayClassificationService::countStayNights($start, $end);
    }

    /** Ngày lịch inclusive — prorate gói tháng. */
    public static function countStayCalendarDays(string|\DateTimeInterface $start, string|\DateTimeInterface $end): int
    {
        return StayClassificationService::countStayCalendarDays($start, $end);
    }

    /**
     * @deprecated Use countStayNights() for nightly rates; countStayCalendarDays() for month prorate.
     */
    public static function countStayDays(string|\DateTimeInterface $start, string|\DateTimeInterface $end): int
    {
        return self::countStayNights($start, $end);
    }

    /**
     * Số lượng hiển thị trên email/UI theo đơn vị gói giá (đêm / tháng / tuần / năm).
     */
    public static function resolveStayDurationCount(
        string|\DateTimeInterface $start,
        string|\DateTimeInterface $end,
        string $priceUnit = 'night',
    ): int {
        $normalized = strtolower(trim($priceUnit));
        $nights = self::countStayNights($start, $end);
        $calendarDays = self::countStayCalendarDays($start, $end);

        return match ($normalized) {
            'month' => max(1, (int) round($calendarDays / self::DAYS_PER_MONTH)),
            'week'  => max(1, (int) round($nights / self::DAYS_PER_WEEK)),
            'year'  => max(1, (int) round($nights / self::DAYS_PER_YEAR)),
            default => $nights,
        };
    }

    public static function computeRoomStayTotal(
        string|\DateTimeInterface $start,
        string|\DateTimeInterface $end,
        float $unitPrice,
        string $unit = 'night',
    ): float {
        if ($unitPrice <= 0) {
            return 0.0;
        }

        $normalizedUnit = strtolower(trim($unit));
        $nights = self::countStayNights($start, $end);
        $calendarDays = self::countStayCalendarDays($start, $end);

        $roomStay = match ($normalizedUnit) {
            'month' => $unitPrice * $calendarDays / self::DAYS_PER_MONTH,
            'week'  => $unitPrice * $nights / self::DAYS_PER_WEEK,
            'year'  => $unitPrice * $nights / self::DAYS_PER_YEAR,
            default => $unitPrice * $nights,
        };

        return round($roomStay, 2);
    }

    public static function computeRoomStayTotalForBooking(Booking $booking): float
    {
        $startRaw = $booking->getRawOriginal('start_date') ?? $booking->start_date;
        $endRaw = $booking->getRawOriginal('end_date') ?? $booking->end_date;

        $booking->loadMissing('price');
        $unitPrice = (float) ($booking->price?->price ?? 0);
        $unit = (string) ($booking->price?->unit ?? 'night');

        return self::computeRoomStayTotal($startRaw, $endRaw, $unitPrice, $unit);
    }

    public static function computeServicesTotalForBooking(Booking $booking): float
    {
        $booking->loadMissing('services');

        return (float) $booking->services->sum(
            static fn ($service): float => (float) ($service->price ?? 0)
        );
    }

    public static function computeGrandTotalForBooking(Booking $booking): float
    {
        return round(
            self::computeRoomStayTotalForBooking($booking) + self::computeServicesTotalForBooking($booking),
            2
        );
    }

    /**
     * Chọn room_prices.id theo thời lượng (khớp FE resolveStayPriceQuote / seeder ResolvesBookingPriceId).
     */
    public static function resolveRoomPriceIdForStay(
        int $roomId,
        string|\DateTimeInterface $start,
        string|\DateTimeInterface $end,
    ): ?int {
        $stayNights = self::countStayNights($start, $end);
        $preferMonth = $stayNights >= StayClassificationService::LONG_TERM_NIGHTS_THRESHOLD;

        $prices = RoomPrice::query()
            ->where('room_id', $roomId)
            ->orderBy('id')
            ->get(['id', 'unit']);

        if ($prices->isEmpty()) {
            return null;
        }

        if (!$preferMonth) {
            $day = $prices->first(
                static fn (RoomPrice $row): bool => strtolower((string) $row->unit) === 'night',
            );
            if ($day !== null) {
                return $day->id;
            }

            // Căn hộ DV: chỉ có gói tháng — prorate theo ngày (khớp FE filterPriceRowsForStayDuration).
            $month = $prices->first(
                static fn (RoomPrice $row): bool => strtolower((string) $row->unit) === 'month',
            );
            if ($month !== null) {
                return $month->id;
            }

            return $prices->first()?->id;
        }

        $month = $prices->first(
            static fn (RoomPrice $row): bool => strtolower((string) $row->unit) === 'month',
        );
        if ($month !== null) {
            return $month->id;
        }

        $day = $prices->first(
            static fn (RoomPrice $row): bool => strtolower((string) $row->unit) === 'night',
        );

        return $day?->id ?? $prices->first()?->id;
    }

    public static function computeRoomStayTotalForRoomPrice(
        string|\DateTimeInterface $start,
        string|\DateTimeInterface $end,
        ?RoomPrice $roomPrice,
    ): float {
        if ($roomPrice === null) {
            return 0.0;
        }

        return self::computeRoomStayTotal(
            $start,
            $end,
            (float) $roomPrice->price,
            (string) ($roomPrice->unit ?? 'night'),
        );
    }

    /**
     * Trường giá dùng cho email xác nhận đặt phòng — khớp domain thuê dài hạn (đợt 1 = tháng đầu + cọc).
     *
     * @return array{
     *     room_stay_amount: float,
     *     services_total: float,
     *     total_amount: float,
     *     room_deposit: float,
     *     price_unit: string,
     *     unit_price: float,
     *     total_days: int,
     *     is_monthly_lease: bool,
     *     first_month_rent: float|null,
     *     remaining_rent: float|null,
     *     installment1_total: float|null,
     *     amount_due_now: float,
     * }
     */
    public static function buildEmailPricingFields(
        string|\DateTimeInterface $start,
        string|\DateTimeInterface $end,
        ?RoomPrice $roomPrice,
        float $servicesTotal,
        float $bookingDepositAmount,
    ): array {
        $priceUnit = strtolower(trim((string) ($roomPrice?->unit ?? 'night')));
        $unitPrice = (float) ($roomPrice?->price ?? 0);
        $roomStayTotal = self::computeRoomStayTotalForRoomPrice($start, $end, $roomPrice);
        $servicesTotal = round(max(0.0, $servicesTotal), 2);
        $grandTotal = round($roomStayTotal + $servicesTotal, 2);
        $depositAmount = round(max(0.0, $bookingDepositAmount), 2);
        $isMonthlyLease = $priceUnit === 'month';

        $fields = [
            'room_stay_amount'   => $roomStayTotal,
            'services_total'     => $servicesTotal,
            'total_amount'       => $grandTotal,
            'room_deposit'       => $depositAmount,
            'price_unit'         => (string) ($roomPrice?->unit ?? 'night'),
            'unit_price'         => $unitPrice,
            'total_days'         => self::resolveStayDurationCount($start, $end, $priceUnit),
            'is_monthly_lease'   => $isMonthlyLease,
            'first_month_rent'   => null,
            'remaining_rent'     => null,
            'installment1_total' => null,
            'amount_due_now'     => $depositAmount > 0 ? $depositAmount : $grandTotal,
        ];

        if ($isMonthlyLease && $unitPrice > 0) {
            $firstMonthRent = round($unitPrice, 2);
            $remainingRent = max(0.0, round($roomStayTotal - $firstMonthRent, 2));
            $installment1Total = round($firstMonthRent + $depositAmount + $servicesTotal, 2);

            $fields['first_month_rent'] = $firstMonthRent;
            $fields['remaining_rent'] = $remainingRent;
            $fields['installment1_total'] = $installment1Total;
            $fields['amount_due_now'] = $depositAmount > 0
                ? $depositAmount
                : $installment1Total;
        }

        return $fields;
    }
}

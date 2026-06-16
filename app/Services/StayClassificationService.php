<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;

/**
 * REQ-STAY-001 — phân loại lưu trú & đếm đêm (khớp SRS v2.1, FE stayClassification.ts).
 */
final class StayClassificationService
{
    public const int LONG_TERM_NIGHTS_THRESHOLD = 30;

    public static function countStayNights(string|\DateTimeInterface $start, string|\DateTimeInterface $end): int
    {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->startOfDay();
        $nights = (int) $startDate->diffInDays($endDate);

        return max(1, $nights);
    }

    /** Ngày lịch inclusive — prorate gói tháng. */
    public static function countStayCalendarDays(string|\DateTimeInterface $start, string|\DateTimeInterface $end): int
    {
        $nights = self::countStayNights($start, $end);

        return max(1, $nights + 1);
    }

    public static function isLongTermLeaseBooking(
        ?string $propertyTypeSlug,
        int $stayNights,
        string $priceUnit = 'night',
    ): bool {
        $slug = strtolower(trim($propertyTypeSlug ?? ''));
        $nights = max(1, $stayNights);
        $unit = strtolower(trim($priceUnit));

        if (in_array($slug, ['khach-san-hotel', 'nha-nghi-guesthouse'], true)) {
            return false;
        }

        if ($slug === 'homestay-co-chia-phong') {
            return $nights >= self::LONG_TERM_NIGHTS_THRESHOLD;
        }

        if (
            $slug === 'can-ho-dich-vu-theo-phong'
            || str_contains($slug, 'can-ho')
            || str_contains($slug, 'apartment')
        ) {
            return $nights >= self::LONG_TERM_NIGHTS_THRESHOLD || $unit === 'month';
        }

        return false;
    }
}

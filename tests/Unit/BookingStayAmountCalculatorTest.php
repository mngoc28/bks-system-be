<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\BookingStayAmountCalculator;
use App\Services\StayClassificationService;
use PHPUnit\Framework\TestCase;

final class BookingStayAmountCalculatorTest extends TestCase
{
    public function test_count_stay_nights_checkout_exclusive(): void
    {
        $this->assertSame(2, BookingStayAmountCalculator::countStayNights('2026-06-21', '2026-06-23'));
    }

    public function test_count_stay_calendar_days_inclusive_for_month_prorate(): void
    {
        $this->assertSame(3, BookingStayAmountCalculator::countStayCalendarDays('2026-06-21', '2026-06-23'));
    }

    public function test_compute_room_stay_total_daily_uses_nights(): void
    {
        $total = BookingStayAmountCalculator::computeRoomStayTotal(
            '2026-06-21',
            '2026-06-23',
            740_856.0,
            'night',
        );

        $this->assertSame(1_481_712.0, $total);
    }

    public function test_guesthouse_is_never_long_term_lease(): void
    {
        $this->assertFalse(
            StayClassificationService::isLongTermLeaseBooking('nha-nghi-guesthouse', 2, 'night'),
        );
    }

    public function test_apartment_two_nights_day_rate_is_short_term(): void
    {
        $this->assertFalse(
            StayClassificationService::isLongTermLeaseBooking('can-ho-dich-vu-theo-phong', 2, 'night'),
        );
    }

    public function test_homestay_thirty_nights_is_long_term(): void
    {
        $this->assertTrue(
            StayClassificationService::isLongTermLeaseBooking('homestay-co-chia-phong', 30, 'night'),
        );
    }
}

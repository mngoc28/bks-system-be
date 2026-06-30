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

    public function test_resolve_stay_duration_count_monthly_from_calendar_days(): void
    {
        $this->assertSame(
            6,
            BookingStayAmountCalculator::resolveStayDurationCount('2026-07-01', '2026-12-28', 'month'),
        );
    }

    public function test_resolve_stay_duration_count_nightly_uses_nights(): void
    {
        $this->assertSame(
            2,
            BookingStayAmountCalculator::resolveStayDurationCount('2026-06-21', '2026-06-23', 'night'),
        );
    }

    public function test_build_email_pricing_fields_monthly_lease_breakdown(): void
    {
        $roomPrice = new \App\Models\RoomPrice([
            'price' => 26_637_167.0,
            'unit' => 'month',
        ]);

        $fields = BookingStayAmountCalculator::buildEmailPricingFields(
            '2026-07-01',
            '2026-12-28',
            $roomPrice,
            1_000_000.0,
            850_000.0,
        );

        $this->assertTrue($fields['is_monthly_lease']);
        $this->assertSame(6, $fields['total_days']);
        $this->assertSame(26_637_167.0, $fields['first_month_rent']);
        $this->assertSame(850_000.0, $fields['room_deposit']);
        $this->assertSame(850_000.0, $fields['amount_due_now']);
        $this->assertGreaterThan($fields['first_month_rent'], $fields['room_stay_amount']);
        $this->assertSame(
            round($fields['first_month_rent'] + 850_000.0 + 1_000_000.0, 2),
            $fields['installment1_total'],
        );
    }

    public function test_homestay_thirty_nights_is_long_term(): void
    {
        $this->assertTrue(
            StayClassificationService::isLongTermLeaseBooking('homestay-co-chia-phong', 30, 'night'),
        );
    }
}

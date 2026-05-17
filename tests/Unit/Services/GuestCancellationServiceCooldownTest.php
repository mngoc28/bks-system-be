<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\GuestCancellationService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

final class GuestCancellationServiceCooldownTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_cooldown_returns_null_when_elapsed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 14:00:00'));
        $last = Carbon::parse('2026-05-14 12:00:00');

        $this->assertNull(GuestCancellationService::cancelRequestCooldownRemainingSeconds($last, 3600));
    }

    public function test_cooldown_returns_positive_seconds_when_inside_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-14 12:30:00'));
        $last = Carbon::parse('2026-05-14 12:00:00');

        $this->assertSame(1800, GuestCancellationService::cancelRequestCooldownRemainingSeconds($last, 3600));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Bcp;

use App\Support\Bcp\CancellationPolicyTierMatcher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class CancellationPolicyTierMatcherTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_first_matching_picks_highest_min_window(): void
    {
        $tiers = Collection::make([
            [
                'id'                       => 1,
                'hours_before_checkin_min' => 168,
                'hours_before_checkin_max' => null,
                'fee_percent'              => 10.00,
            ],
            [
                'id'                       => 2,
                'hours_before_checkin_min' => 48,
                'hours_before_checkin_max' => 167,
                'fee_percent'              => 25.00,
            ],
            [
                'id'                       => 3,
                'hours_before_checkin_min' => 0,
                'hours_before_checkin_max' => 47,
                'fee_percent'              => 50.00,
            ],
        ])->sortByDesc('hours_before_checkin_min')->values();

        $picked = CancellationPolicyTierMatcher::firstMatchingTierRow($tiers, 60);
        $this->assertNotNull($picked);
        $this->assertSame(2, (int) $picked['id']);
        $this->assertSame(25.0, (float) $picked['fee_percent']);
    }

    public function test_hours_before_checkin_zero_when_at_after_checkin_start(): void
    {
        $tz = 'UTC';
        $start = Carbon::parse('2026-06-20', $tz);
        $at = Carbon::parse('2026-06-20 15:00:00', $tz);

        $this->assertSame(0, CancellationPolicyTierMatcher::hoursBeforeCheckinStart($start, $at, $tz));
    }

    public function test_hours_before_matches_phase_b5_short_scenario(): void
    {
        $tz = 'UTC';
        $start = Carbon::parse('2026-06-20', $tz);
        $at = Carbon::parse('2026-06-17 12:00:00', $tz);

        $this->assertSame(60, CancellationPolicyTierMatcher::hoursBeforeCheckinStart($start, $at, $tz));
    }

    public function test_stay_kind_long_when_nights_meets_threshold(): void
    {
        $tz = 'UTC';
        $start = Carbon::parse('2026-02-10', $tz);
        $end = Carbon::parse('2026-03-20', $tz);

        $this->assertSame('long', CancellationPolicyTierMatcher::stayKind($start, $end, 30, $tz));
    }

    public function test_stay_kind_short_below_threshold(): void
    {
        $tz = 'UTC';
        $start = Carbon::parse('2026-06-20', $tz);
        $end = Carbon::parse('2026-06-22', $tz);

        $this->assertSame('short', CancellationPolicyTierMatcher::stayKind($start, $end, 30, $tz));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\BookingStatus;
use App\Services\ConflictChecker;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests cho logic interval của ConflictChecker (Phase 3 — T3.16).
 *
 * Chỉ cover phần `intervalsOverlap` thuần — phần query Eloquent đã được
 * verify gián tiếp qua RoomBlockServiceTest và feature tests sau này.
 *
 * BCP (Phase B1): `findConflicts` loại trừ chỉ CANCELLED/COMPLETED — status 4
 * (`PENDING_CANCELLATION`) vẫn conflict-active; test dưới khóa contract tập loại trừ.
 *
 * Quy tắc business: `end_date` exclusive — back-to-back (a.end == b.start)
 * KHÔNG được tính là conflict.
 */
final class ConflictCheckerTest extends TestCase
{
    public function test_overlapping_intervals_are_detected(): void
    {
        $this->assertTrue(ConflictChecker::intervalsOverlap('2026-05-10', '2026-05-15', '2026-05-12', '2026-05-20'));
        $this->assertTrue(ConflictChecker::intervalsOverlap('2026-05-12', '2026-05-20', '2026-05-10', '2026-05-15'));
    }

    public function test_one_interval_fully_contains_other(): void
    {
        $this->assertTrue(ConflictChecker::intervalsOverlap('2026-05-10', '2026-05-30', '2026-05-15', '2026-05-20'));
        $this->assertTrue(ConflictChecker::intervalsOverlap('2026-05-15', '2026-05-20', '2026-05-10', '2026-05-30'));
    }

    public function test_back_to_back_intervals_are_not_conflicts(): void
    {
        $this->assertFalse(ConflictChecker::intervalsOverlap('2026-05-10', '2026-05-15', '2026-05-15', '2026-05-20'));
        $this->assertFalse(ConflictChecker::intervalsOverlap('2026-05-15', '2026-05-20', '2026-05-10', '2026-05-15'));
    }

    public function test_disjoint_intervals_are_not_conflicts(): void
    {
        $this->assertFalse(ConflictChecker::intervalsOverlap('2026-05-10', '2026-05-12', '2026-05-15', '2026-05-20'));
        $this->assertFalse(ConflictChecker::intervalsOverlap('2026-05-15', '2026-05-20', '2026-05-10', '2026-05-12'));
    }

    public function test_identical_intervals_overlap(): void
    {
        $this->assertTrue(ConflictChecker::intervalsOverlap('2026-05-10', '2026-05-15', '2026-05-10', '2026-05-15'));
    }

    public function test_single_day_overlap_within_window(): void
    {
        $this->assertTrue(ConflictChecker::intervalsOverlap('2026-05-10', '2026-05-15', '2026-05-14', '2026-05-15'));
    }

    public function test_pending_cancellation_not_in_conflict_exclusion_list(): void
    {
        $excluded = [
            BookingStatus::CANCELLED->value,
            BookingStatus::COMPLETED->value,
        ];

        $this->assertNotContains(BookingStatus::PENDING_CANCELLATION->value, $excluded, 'BCP: status 4 must remain conflict-active.');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\BookingTimelineEvent;
use App\Repositories\BookingTimelineRepository\BookingTimelineRepositoryInterface;
use App\Services\BookingTimelineService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Unit tests for BookingTimelineService.
 *
 * Verifies that each public recordX method writes the expected event_type,
 * status transition, and metadata shape to the repository.
 *
 * Mapped to plan task T1.7 / T1.14.
 */
final class BookingTimelineServiceTest extends TestCase
{
    /** @var BookingTimelineRepositoryInterface&MockInterface */
    private BookingTimelineRepositoryInterface $repository;

    private BookingTimelineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(BookingTimelineRepositoryInterface::class);
        $this->service = new BookingTimelineService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_record_confirmed_writes_correct_payload(): void
    {
        $captured = [];
        $this->repository->shouldReceive('append')
            ->once()
            ->andReturnUsing(function (array $payload) use (&$captured): BookingTimelineEvent {
                $captured = $payload;
                return new BookingTimelineEvent();
            });

        $this->service->recordConfirmed(42, 7, ['contract_type' => 'LEASE_AGREEMENT']);

        $this->assertSame(42, $captured['booking_id']);
        $this->assertSame(7, $captured['actor_id']);
        $this->assertSame('confirmed', $captured['event_type']);
        $this->assertSame('pending', $captured['from_status']);
        $this->assertSame('confirmed', $captured['to_status']);
        $this->assertNull($captured['note']);
        $this->assertSame(['contract_type' => 'LEASE_AGREEMENT'], $captured['metadata']);
    }

    public function test_record_cancelled_requires_reason_and_records_from_status(): void
    {
        $captured = [];
        $this->repository->shouldReceive('append')
            ->once()
            ->andReturnUsing(function (array $payload) use (&$captured): BookingTimelineEvent {
                $captured = $payload;
                return new BookingTimelineEvent();
            });

        $this->service->recordCancelled(11, 'Customer requested', 'confirmed', 5, ['role' => 'partner']);

        $this->assertSame('cancelled', $captured['event_type']);
        $this->assertSame('confirmed', $captured['from_status']);
        $this->assertSame('cancelled', $captured['to_status']);
        $this->assertSame('Customer requested', $captured['note']);
        $this->assertSame(['role' => 'partner'], $captured['metadata']);
    }

    public function test_record_no_show_keeps_to_status_confirmed_for_kpi(): void
    {
        $captured = [];
        $this->repository->shouldReceive('append')
            ->once()
            ->andReturnUsing(function (array $payload) use (&$captured): BookingTimelineEvent {
                $captured = $payload;
                return new BookingTimelineEvent();
            });

        $this->service->recordNoShow(99);

        $this->assertSame('no_show', $captured['event_type']);
        $this->assertSame('confirmed', $captured['from_status']);
        $this->assertSame('confirmed', $captured['to_status']);
    }

    public function test_record_backfilled_marks_metadata_for_kpi_exclusion(): void
    {
        $captured = [];
        $this->repository->shouldReceive('append')
            ->once()
            ->andReturnUsing(function (array $payload) use (&$captured): BookingTimelineEvent {
                $captured = $payload;
                return new BookingTimelineEvent();
            });

        $this->service->recordBackfilled(123, ['confirmed_at_source' => 'updated_at']);

        $this->assertSame('backfilled', $captured['event_type']);
        $this->assertNull($captured['actor_id']);
        $this->assertTrue($captured['metadata']['backfilled']);
        $this->assertSame('updated_at', $captured['metadata']['confirmed_at_source']);
    }

    public function test_metadata_is_null_when_caller_passes_empty_array(): void
    {
        $captured = [];
        $this->repository->shouldReceive('append')
            ->once()
            ->andReturnUsing(function (array $payload) use (&$captured): BookingTimelineEvent {
                $captured = $payload;
                return new BookingTimelineEvent();
            });

        $this->service->recordCheckedIn(1);

        $this->assertNull($captured['metadata']);
    }
}

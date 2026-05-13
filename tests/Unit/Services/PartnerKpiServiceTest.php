<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Services\PartnerKpiService;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for PartnerKpiService.
 *
 * Note: methods that touch DB (avg-confirm-time, pending count) are exercised
 * indirectly via mocked repository contracts; the SQL paths are covered by
 * feature tests when the test database is available.
 *
 * Mapped to plan task T1.12 / T1.14.
 */
final class PartnerKpiServiceTest extends TestCase
{
    /** @var BookingRepositoryInterface&MockInterface */
    private BookingRepositoryInterface $bookingRepository;

    /** @var RoomsRepositoryInterface&MockInterface */
    private RoomsRepositoryInterface $roomsRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingRepository = Mockery::mock(BookingRepositoryInterface::class);
        $this->roomsRepository = Mockery::mock(RoomsRepositoryInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_commission_rate_is_five_percent(): void
    {
        $this->assertSame(0.05, PartnerKpiService::COMMISSION_RATE);
    }

    public function test_cache_ttl_is_sixty_seconds(): void
    {
        $this->assertSame(60, PartnerKpiService::CACHE_TTL_SECONDS);
    }

    public function test_cache_keys_include_dashboard_and_phase_four_charts(): void
    {
        $this->assertSame([
            'partner:7:kpi:dashboard',
            'partner:7:kpi:charts:occupancy',
            'partner:7:kpi:charts:gmv',
        ], PartnerKpiService::cacheKeysForPartner(7));
    }

    public function test_occupancy_rate_handles_zero_rooms_gracefully(): void
    {
        $this->roomsRepository->shouldReceive('countRoomsForPartner')->with(7)->andReturn(0);
        $this->roomsRepository->shouldReceive('getEmptyRoomsForPartner')->with(7)->andReturn(0);
        $this->bookingRepository->shouldReceive('getRevenueByMonthForPartner')
            ->andReturn(new Collection([]));
        $this->bookingRepository->shouldReceive('countBookingsForPartner')
            ->andReturn(0);

        $service = $this->makeServiceWithStubbedDb();
        $result = $service->computeDashboard(7);

        $this->assertSame(0.0, $result['occupancyRate']);
        $this->assertSame(0, $result['totalRooms']);
        $this->assertSame(0, $result['occupiedRooms']);
    }

    public function test_occupancy_and_net_revenue_are_calculated_correctly(): void
    {
        $this->roomsRepository->shouldReceive('countRoomsForPartner')->andReturn(10);
        $this->roomsRepository->shouldReceive('getEmptyRoomsForPartner')->andReturn(3);
        $this->bookingRepository->shouldReceive('getRevenueByMonthForPartner')
            ->andReturn(new Collection([
                ['month' => '2026-05', 'revenue' => 8_000_000.0],
                ['month' => '2026-04', 'revenue' => 2_000_000.0],
            ]));
        $this->bookingRepository->shouldReceive('countBookingsForPartner')->andReturn(4);

        $service = $this->makeServiceWithStubbedDb();
        $result = $service->computeDashboard(7);

        $this->assertSame(70.0, $result['occupancyRate']);
        $this->assertSame(7, $result['occupiedRooms']);
        $this->assertSame(10, $result['totalRooms']);
        $this->assertSame(10_000_000.0, $result['gmvMtd']);
        $this->assertSame(9_500_000.0, $result['netRevenueMtd']);
        $this->assertSame(0.05, $result['commissionRate']);
        $this->assertSame(4, $result['pendingCount']);
        $this->assertArrayHasKey('calculatedAt', $result);
    }

    /**
     * Build a PartnerKpiService that returns a fixed avg-confirm-seconds so we
     * do not exercise the DB facade in pure unit tests.
     */
    private function makeServiceWithStubbedDb(?int $avgSeconds = 120): PartnerKpiService
    {
        return new class ($this->bookingRepository, $this->roomsRepository, $avgSeconds) extends PartnerKpiService {
            public function __construct(
                BookingRepositoryInterface $bookingRepository,
                RoomsRepositoryInterface $roomsRepository,
                private readonly ?int $stubAvgSeconds,
            ) {
                parent::__construct($bookingRepository, $roomsRepository);
            }

            protected function computeAvgConfirmSeconds(int $partnerId): ?int
            {
                return $this->stubAvgSeconds;
            }
        };
    }
}

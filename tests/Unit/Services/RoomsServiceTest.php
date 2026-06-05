<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\RoomBlock;
use App\Repositories\RoomAmenityRepository\RoomAmenityRepository;
use App\Repositories\RoomPriceRepository\RoomPriceRepository;
use App\Repositories\RoomServiceRepository\RoomServiceRepository;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Services\ConflictChecker;
use App\Services\RoomAmenityService;
use App\Services\RoomPriceService;
use App\Services\RoomsService;
use App\Services\RoomServiceService;
use App\Services\RoomTouristSummaryService;
use App\Services\UtilityFeeService;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Unit tests for RoomsService::handleGetBookedDates (Availability-First Booking).
 */
final class RoomsServiceTest extends TestCase
{
    /** @var RoomsRepositoryInterface&MockInterface */
    private $roomsRepository;

    /** @var RoomServiceRepository&MockInterface */
    private $roomServiceRepository;

    /** @var RoomAmenityRepository&MockInterface */
    private $roomAmenityRepository;

    /** @var RoomPriceRepository&MockInterface */
    private $roomPriceRepository;

    /** @var RoomServiceService&MockInterface */
    private $roomServiceService;

    /** @var RoomAmenityService&MockInterface */
    private $roomAmenityService;

    /** @var RoomPriceService&MockInterface */
    private $roomPriceService;

    /** @var UtilityFeeService&MockInterface */
    private $utilityFeeService;

    private RoomTouristSummaryService $roomTouristSummaryService;

    /** @var ConflictChecker&MockInterface */
    private $conflictChecker;

    private RoomsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roomsRepository = Mockery::mock(RoomsRepositoryInterface::class);
        $this->roomServiceRepository = Mockery::mock(RoomServiceRepository::class);
        $this->roomAmenityRepository = Mockery::mock(RoomAmenityRepository::class);
        $this->roomPriceRepository = Mockery::mock(RoomPriceRepository::class);
        $this->roomServiceService = Mockery::mock(RoomServiceService::class);
        $this->roomAmenityService = Mockery::mock(RoomAmenityService::class);
        $this->roomPriceService = Mockery::mock(RoomPriceService::class);
        $this->utilityFeeService = Mockery::mock(UtilityFeeService::class);
        $this->roomTouristSummaryService = app(RoomTouristSummaryService::class);
        $this->conflictChecker = Mockery::mock(ConflictChecker::class);

        $this->service = new RoomsService(
            $this->roomsRepository,
            $this->roomServiceRepository,
            $this->roomAmenityRepository,
            $this->roomPriceRepository,
            $this->roomServiceService,
            $this->roomAmenityService,
            $this->roomPriceService,
            $this->utilityFeeService,
            $this->roomTouristSummaryService,
            $this->conflictChecker
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_get_booked_dates_returns_correct_occupied_dates(): void
    {
        $roomId = 456;
        $request = new Request();

        // 1. Setup mock Booking occupied from 2026-06-10 to 2026-06-12 (nights of 10 and 11)
        $booking = new Booking();
        $booking->setRawAttributes([
            'start_date' => '2026-06-10',
            'end_date'   => '2026-06-12',
        ]);

        // 2. Setup mock RoomBlock occupied from 2026-06-15 to 2026-06-17 (nights of 15 and 16)
        $block = new RoomBlock();
        $block->setRawAttributes([
            'start_date' => '2026-06-15',
            'end_date'   => '2026-06-17',
        ]);

        $this->conflictChecker->shouldReceive('findConflicts')
            ->once()
            ->with($roomId, Mockery::any(), Mockery::any())
            ->andReturn([
                'bookings' => collect([$booking]),
                'blocks'   => collect([$block]),
            ]);

        $result = $this->service->handleGetBookedDates($roomId, $request);

        $this->assertTrue($result['success']);
        $this->assertSame(
            ['2026-06-10', '2026-06-11', '2026-06-15', '2026-06-16'],
            $result['data']
        );
    }

    public function test_handle_get_booked_dates_merges_and_sorts_correctly(): void
    {
        $roomId = 456;
        $request = new Request();

        $booking1 = new Booking();
        $booking1->setRawAttributes([
            'start_date' => '2026-06-10',
            'end_date'   => '2026-06-12', // nights of 10, 11
        ]);

        $booking2 = new Booking();
        $booking2->setRawAttributes([
            'start_date' => '2026-06-11',
            'end_date'   => '2026-06-13', // nights of 11, 12
        ]);

        $block = new RoomBlock();
        $block->setRawAttributes([
            'start_date' => '2026-06-08',
            'end_date'   => '2026-06-10', // nights of 8, 9
        ]);

        $this->conflictChecker->shouldReceive('findConflicts')
            ->once()
            ->andReturn([
                'bookings' => collect([$booking1, $booking2]),
                'blocks'   => collect([$block]),
            ]);

        $result = $this->service->handleGetBookedDates($roomId, $request);

        $this->assertTrue($result['success']);
        $this->assertSame(
            ['2026-06-08', '2026-06-09', '2026-06-10', '2026-06-11', '2026-06-12'],
            $result['data']
        );
    }

    public function test_handle_get_booked_dates_handles_exceptions_gracefully(): void
    {
        $roomId = 456;
        $request = new Request();

        $this->conflictChecker->shouldReceive('findConflicts')
            ->once()
            ->andThrow(new \Exception('Database connection lost'));

        $result = $this->service->handleGetBookedDates($roomId, $request);

        $this->assertFalse($result['success']);
        $this->assertSame('Lấy danh sách ngày bận thất bại.', $result['message']);
    }
}

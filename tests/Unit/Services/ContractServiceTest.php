<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Events\ContractRenewalReminderQueued;
use App\Models\Booking;
use App\Models\Building;
use App\Models\Contract;
use App\Models\Room;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\ContractRepository\ContractRepositoryInterface;
use App\Services\ContractService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Unit tests for ContractService (Partner Portal 360 Phase 5, T5.1 + T5.2).
 *
 * Repositories are mocked and DB transactions short-circuited so the suite
 * stays driver-agnostic and works on the array cache used by phpunit.xml.
 */
final class ContractServiceTest extends TestCase
{
    /** @var ContractRepositoryInterface&MockInterface */
    private $contractRepository;

    /** @var BookingRepositoryInterface&MockInterface */
    private $bookingRepository;

    private ContractService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contractRepository = Mockery::mock(ContractRepositoryInterface::class);
        $this->bookingRepository  = Mockery::mock(BookingRepositoryInterface::class);

        $this->service = new ContractService(
            $this->contractRepository,
            $this->bookingRepository,
        );

        Event::fake([ContractRenewalReminderQueued::class]);
        Auth::shouldReceive('id')->andReturn(7);
        DB::shouldReceive('transaction')->andReturnUsing(fn (\Closure $cb) => $cb());
        Gate::shouldReceive('allows')->andReturn(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_set_renewal_reminder_succeeds_for_active_lease_contract(): void
    {
        $contract = $this->makeContract(id: 101, type: 'LEASE_AGREEMENT');

        $this->contractRepository->shouldReceive('find')->with(101)->andReturn($contract);
        $this->contractRepository->shouldReceive('update')
            ->once()
            ->withArgs(function (int $id, array $attrs): bool {
                return $id === 101
                    && array_key_exists('renewal_reminder_at', $attrs)
                    && $attrs['updated_by'] === 7;
            })
            ->andReturn(true);

        $remindAt = Carbon::create(2026, 5, 12, 6, 0, 0);
        $result = $this->service->setRenewalReminder(101, $remindAt);

        $this->assertTrue($result['success']);
        $this->assertNotNull($contract->renewal_reminder_at);
        $this->assertSame($remindAt->toIso8601String(), $contract->renewal_reminder_at->toIso8601String());

        Event::assertDispatched(ContractRenewalReminderQueued::class, function (ContractRenewalReminderQueued $e) {
            return (int) $e->contract->id === 101 && $e->partnerId === 7;
        });
    }

    public function test_set_renewal_reminder_rejects_non_lease_contract(): void
    {
        $contract = $this->makeContract(id: 102, type: 'TERMS_AND_CONDITIONS');
        $this->contractRepository->shouldReceive('find')->with(102)->andReturn($contract);

        $result = $this->service->setRenewalReminder(102, Carbon::now());

        $this->assertFalse($result['success']);
        $this->assertSame('CONTRACT_NOT_LEASE', $result['code']);
        Event::assertNotDispatched(ContractRenewalReminderQueued::class);
    }

    public function test_set_renewal_reminder_rejects_terminated_contract(): void
    {
        $contract = $this->makeContract(id: 103, type: 'LEASE_AGREEMENT');
        $contract->terminated_at = Carbon::now()->subDay();
        $this->contractRepository->shouldReceive('find')->with(103)->andReturn($contract);

        $result = $this->service->setRenewalReminder(103, Carbon::now());

        $this->assertFalse($result['success']);
        $this->assertSame('CONTRACT_TERMINATED', $result['code']);
        Event::assertNotDispatched(ContractRenewalReminderQueued::class);
    }

    public function test_set_renewal_reminder_returns_not_found_for_missing_contract(): void
    {
        $this->contractRepository->shouldReceive('find')->with(404)->andReturn(null);

        $result = $this->service->setRenewalReminder(404, Carbon::now());

        $this->assertFalse($result['success']);
        $this->assertSame('CONTRACT_NOT_FOUND', $result['code']);
    }

    public function test_terminate_succeeds_with_valid_reason(): void
    {
        $contract = $this->makeContract(id: 201, type: 'LEASE_AGREEMENT');
        $this->contractRepository->shouldReceive('find')->with(201)->andReturn($contract);
        $this->contractRepository->shouldReceive('update')
            ->once()
            ->withArgs(function (int $id, array $attrs): bool {
                return $id === 201
                    && $attrs['termination_reason'] === 'Khách thanh lý hợp đồng trước hạn.'
                    && array_key_exists('terminated_at', $attrs);
            })
            ->andReturn(true);

        $result = $this->service->terminate(201, 'Khách thanh lý hợp đồng trước hạn.');

        $this->assertTrue($result['success']);
        $this->assertNotNull($contract->terminated_at);
        $this->assertSame('Khách thanh lý hợp đồng trước hạn.', $contract->termination_reason);
    }

    public function test_terminate_rejects_empty_reason(): void
    {
        $contract = $this->makeContract(id: 202, type: 'LEASE_AGREEMENT');
        $this->contractRepository->shouldReceive('find')->with(202)->andReturn($contract);

        $result = $this->service->terminate(202, '   ');

        $this->assertFalse($result['success']);
        $this->assertSame('CONTRACT_TERMINATE_REASON_REQUIRED', $result['code']);
    }

    public function test_terminate_is_idempotent_for_already_terminated_contract(): void
    {
        $contract = $this->makeContract(id: 203, type: 'LEASE_AGREEMENT');
        $contract->terminated_at = Carbon::now()->subHour();
        $this->contractRepository->shouldReceive('find')->with(203)->andReturn($contract);

        $result = $this->service->terminate(203, 'Lý do hợp lệ ok.');

        $this->assertFalse($result['success']);
        $this->assertSame('CONTRACT_ALREADY_TERMINATED', $result['code']);
    }

    public function test_process_due_reminders_tags_each_returned_contract(): void
    {
        $a = $this->makeContract(id: 301, type: 'LEASE_AGREEMENT');
        $b = $this->makeContract(id: 302, type: 'LEASE_AGREEMENT');

        $this->contractRepository->shouldReceive('getLongTermContractsDueForReminder')
            ->with(30)
            ->once()
            ->andReturn(new Collection([$a, $b]));

        $this->contractRepository->shouldReceive('find')->with(301)->andReturn($a);
        $this->contractRepository->shouldReceive('find')->with(302)->andReturn($b);
        $this->contractRepository->shouldReceive('update')->times(2)->andReturn(true);

        $count = $this->service->processDueReminders();

        $this->assertSame(2, $count);
        $this->assertNotNull($a->renewal_reminder_at);
        $this->assertNotNull($b->renewal_reminder_at);
        Event::assertDispatched(ContractRenewalReminderQueued::class, 2);
    }

    /**
     * Builds an in-memory Contract with the booking/room/building chain so the
     * dispatcher can derive partner & property ids without touching the DB.
     */
    private function makeContract(int $id, string $type): Contract
    {
        $building = new Building();
        $building->id = 500;
        $building->user_id = 7;

        $room = new Room();
        $room->id = 11;
        $room->building_id = 500;
        $room->setRelation('building', $building);

        $booking = new Booking();
        $booking->id = 1000 + $id;
        $booking->room_id = 11;
        $booking->setRawAttributes([
            'id'         => $booking->id,
            'room_id'    => 11,
            'start_date' => '2026-04-01',
            'end_date'   => '2026-05-31',
        ], true);
        $booking->setRelation('room', $room);

        $contract = new Contract();
        $contract->id = $id;
        $contract->booking_id = $booking->id;
        $contract->contract_type = $type;
        $contract->setRelation('booking', $booking);
        $contract->exists = true;

        return $contract;
    }
}

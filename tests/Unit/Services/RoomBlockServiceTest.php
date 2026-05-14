<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Events\RoomBlockChanged;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomBlock;
use App\Repositories\RoomBlockRepository\RoomBlockRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Services\ConflictChecker;
use App\Services\RoomBlockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Unit tests cho RoomBlockService (Phase 3 — T3.16).
 *
 * Mocks: RoomBlockRepositoryInterface, RoomsRepositoryInterface, ConflictChecker.
 * Facade swap: Auth, Gate, DB, Event để verify behavior mà không cần DB thật.
 */
final class RoomBlockServiceTest extends TestCase
{
    /** @var RoomBlockRepositoryInterface&MockInterface */
    private $blockRepository;

    /** @var RoomsRepositoryInterface&MockInterface */
    private $roomsRepository;

    /** @var ConflictChecker&MockInterface */
    private $conflictChecker;

    private RoomBlockService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blockRepository = Mockery::mock(RoomBlockRepositoryInterface::class);
        $this->roomsRepository = Mockery::mock(RoomsRepositoryInterface::class);
        $this->conflictChecker = Mockery::mock(ConflictChecker::class);

        $this->service = new RoomBlockService(
            $this->blockRepository,
            $this->roomsRepository,
            $this->conflictChecker,
        );

        // Event::fake() trước Auth để tránh AuthServiceProvider rebind 'events'
        // resolve Auth manager khi nó chưa được mock đầy đủ.
        Event::fake([RoomBlockChanged::class]);

        Auth::shouldReceive('id')->andReturn(7);
        DB::shouldReceive('transaction')->andReturnUsing(fn (\Closure $cb) => $cb());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_returns_success_and_dispatches_event_when_no_conflict(): void
    {
        $room = $this->makeRoom(roomId: 11, partnerId: 7);
        $this->roomsRepository->shouldReceive('find')->with(11)->andReturn($room);

        Gate::shouldReceive('denies')->with('createForRoom', $room)->andReturn(false);

        $this->conflictChecker->shouldReceive('findConflicts')
            ->once()
            ->with(11, '2026-05-15', '2026-05-20', null, null, true)
            ->andReturn([
                'bookings'    => collect(),
                'blocks'      => collect(),
                'hasConflict' => false,
            ]);

        $created = new RoomBlock();
        $created->id         = 99;
        $created->room_id    = 11;
        $created->block_type = 'maintenance';

        $this->blockRepository->shouldReceive('create')->once()->andReturn($created);

        $result = $this->service->create([
            'room_id'    => 11,
            'start_date' => '2026-05-15',
            'end_date'   => '2026-05-20',
            'block_type' => 'maintenance',
            'reason'     => 'Sửa máy lạnh',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame($created, $result['data']);

        Event::assertDispatched(RoomBlockChanged::class, function (RoomBlockChanged $e) use ($created) {
            return $e->action === 'created'
                && $e->block->id === $created->id
                && $e->partnerId === 7
                && $e->propertyId === 100;
        });
    }

    public function test_create_returns_conflict_when_overlap_detected(): void
    {
        $room = $this->makeRoom(roomId: 11, partnerId: 7);
        $this->roomsRepository->shouldReceive('find')->with(11)->andReturn($room);
        Gate::shouldReceive('denies')->with('createForRoom', $room)->andReturn(false);

        $existing = new RoomBlock();
        $existing->id         = 55;
        $existing->room_id    = 11;
        $existing->block_type = 'owner_use';
        $existing->setRawAttributes([
            'id'         => 55,
            'start_date' => '2026-05-18',
            'end_date'   => '2026-05-22',
            'block_type' => 'owner_use',
        ], true);

        $this->conflictChecker->shouldReceive('findConflicts')
            ->once()
            ->andReturn([
                'bookings'    => new Collection(),
                'blocks'      => new Collection([$existing]),
                'hasConflict' => true,
            ]);

        $this->blockRepository->shouldNotReceive('create');

        $result = $this->service->create([
            'room_id'    => 11,
            'start_date' => '2026-05-15',
            'end_date'   => '2026-05-20',
            'block_type' => 'maintenance',
            'reason'     => 'Sửa máy lạnh',
        ]);

        $this->assertFalse($result['success']);
        $this->assertSame('ROOM_BLOCK_CONFLICT', $result['code'] ?? null);
        $this->assertIsArray($result['data']);
        $this->assertCount(1, $result['data']['blocks']);
        $this->assertSame(55, $result['data']['blocks'][0]['id']);

        Event::assertNotDispatched(RoomBlockChanged::class);
    }

    public function test_create_returns_unauthorized_when_policy_denies(): void
    {
        $room = $this->makeRoom(roomId: 11, partnerId: 99);
        $this->roomsRepository->shouldReceive('find')->with(11)->andReturn($room);
        Gate::shouldReceive('denies')->with('createForRoom', $room)->andReturn(true);

        $this->conflictChecker->shouldNotReceive('findConflicts');
        $this->blockRepository->shouldNotReceive('create');

        $result = $this->service->create([
            'room_id'    => 11,
            'start_date' => '2026-05-15',
            'end_date'   => '2026-05-20',
            'block_type' => 'maintenance',
            'reason'     => 'X',
        ]);

        $this->assertFalse($result['success']);
        $this->assertNull($result['data']);

        Event::assertNotDispatched(RoomBlockChanged::class);
    }

    public function test_create_rejects_invalid_date_range(): void
    {
        $room = $this->makeRoom(roomId: 11, partnerId: 7);
        $this->roomsRepository->shouldReceive('find')->with(11)->andReturn($room);
        Gate::shouldReceive('denies')->with('createForRoom', $room)->andReturn(false);

        $this->conflictChecker->shouldNotReceive('findConflicts');

        $result = $this->service->create([
            'room_id'    => 11,
            'start_date' => '2026-05-20',
            'end_date'   => '2026-05-15',
            'block_type' => 'maintenance',
            'reason'     => 'X',
        ]);

        $this->assertFalse($result['success']);

        Event::assertNotDispatched(RoomBlockChanged::class);
    }

    public function test_delete_dispatches_event_on_success(): void
    {
        $room = $this->makeRoom(roomId: 11, partnerId: 7);
        $block = new RoomBlock();
        $block->id      = 200;
        $block->room_id = 11;

        $this->blockRepository->shouldReceive('find')->with(200)->andReturn($block);
        Gate::shouldReceive('denies')->with('delete', $block)->andReturn(false);
        $this->roomsRepository->shouldReceive('find')->with(11)->andReturn($room);
        $this->blockRepository->shouldReceive('delete')->once()->with(200);

        $result = $this->service->delete(200);

        $this->assertTrue($result['success']);
        $this->assertSame(['id' => 200], $result['data']);

        Event::assertDispatched(RoomBlockChanged::class, fn (RoomBlockChanged $e) => $e->action === 'deleted');
    }

    public function test_delete_returns_unauthorized_when_policy_denies(): void
    {
        $block = new RoomBlock();
        $block->id      = 200;
        $block->room_id = 11;

        $this->blockRepository->shouldReceive('find')->with(200)->andReturn($block);
        Gate::shouldReceive('denies')->with('delete', $block)->andReturn(true);
        $this->blockRepository->shouldNotReceive('delete');

        $result = $this->service->delete(200);

        $this->assertFalse($result['success']);

        Event::assertNotDispatched(RoomBlockChanged::class);
    }

    private function makeRoom(int $roomId, int $partnerId): Room
    {
        $property = new Property();
        $property->id = 100;
        $property->user_id = $partnerId;

        $room = new Room();
        $room->id = $roomId;
        $room->property_id = 100;
        $room->setRelation('property', $property);

        return $room;
    }
}

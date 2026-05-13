<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\RoomBlockChanged;
use App\Models\Room;
use App\Models\RoomBlock;
use App\Repositories\RoomBlockRepository\RoomBlockRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service quản lý room block (Partner Portal 360 Phase 3).
 *
 * Workflow `create`:
 *   1) Resolve room → kiểm policy `createForRoom`.
 *   2) Trong DB transaction: dùng `ConflictChecker` (lockForUpdate) để chặn
 *      block trùng booking đang active hoặc block khác.
 *   3) Persist + dispatch `RoomBlockChanged(action=created)` qua `safeDispatch`.
 *
 * Workflow `delete`: kiểm policy → xoá → dispatch `action=deleted`.
 */
final class RoomBlockService
{
    public function __construct(
        private readonly RoomBlockRepositoryInterface $blockRepository,
        private readonly RoomsRepositoryInterface $roomsRepository,
        private readonly ConflictChecker $conflictChecker,
    ) {
    }

    /**
     * @param array{
     *     room_id: int,
     *     start_date: string,
     *     end_date: string,
     *     block_type: string,
     *     reason: string,
     *     note?: string|null
     * } $data
     * @return array{success: bool, data: RoomBlock|array<string,mixed>|null, message: string, code?: string}
     */
    public function create(array $data): array
    {
        try {
            $room = $this->roomsRepository->find($data['room_id']);
            if (! $room) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_block.messages.room_not_found'),
                ];
            }

            if (Gate::denies('createForRoom', $room)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_block.messages.unauthorized'),
                ];
            }

            $startDate = $this->normalizeDate($data['start_date']);
            $endDate   = $this->normalizeDate($data['end_date']);
            if ($startDate > $endDate) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_block.messages.invalid_date_range'),
                ];
            }

            $createdBlock = null;
            $conflictPayload = null;
            DB::transaction(function () use ($data, $startDate, $endDate, &$createdBlock, &$conflictPayload): void {
                $conflicts = $this->conflictChecker->findConflicts(
                    (int) $data['room_id'],
                    $startDate,
                    $endDate,
                    null,
                    null,
                    true,
                );

                if ($conflicts['hasConflict']) {
                    $conflictPayload = [
                        'bookings' => $conflicts['bookings']->map(fn ($b) => [
                            'id'         => (int) $b->id,
                            'start_date' => optional($b->start_date)->format('Y-m-d')
                                ?? (string) $b->getRawOriginal('start_date'),
                            'end_date'   => optional($b->end_date)->format('Y-m-d')
                                ?? (string) $b->getRawOriginal('end_date'),
                            'status'     => (int) $b->status,
                        ])->values()->all(),
                        'blocks'   => $conflicts['blocks']->map(fn ($block) => [
                            'id'         => (int) $block->id,
                            'block_type' => (string) $block->block_type,
                            'start_date' => optional($block->start_date)->format('Y-m-d')
                                ?? (string) $block->getRawOriginal('start_date'),
                            'end_date'   => optional($block->end_date)->format('Y-m-d')
                                ?? (string) $block->getRawOriginal('end_date'),
                        ])->values()->all(),
                    ];

                    return;
                }

                $createdBlock = $this->blockRepository->create([
                    'room_id'    => (int) $data['room_id'],
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'block_type' => (string) $data['block_type'],
                    'reason'     => (string) $data['reason'],
                    'note'       => $data['note'] ?? null,
                    'created_by' => Auth::id(),
                ]);
            });

            if ($conflictPayload !== null) {
                return [
                    'success' => false,
                    'data'    => $conflictPayload,
                    'message' => __('room_block.messages.conflict'),
                    'code'    => 'ROOM_BLOCK_CONFLICT',
                ];
            }

            if ($createdBlock !== null) {
                $this->dispatchChanged($createdBlock, $room, 'created');
            }

            return [
                'success' => true,
                'data'    => $createdBlock,
                'message' => __('room_block.messages.created_successfully'),
            ];
        } catch (Throwable $e) {
            Log::error('Room block create failed', [
                'data'  => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('room_block.messages.create_failed'),
            ];
        }
    }

    /**
     * @return array{success: bool, data: array{id:int}|null, message: string}
     */
    public function delete(int $blockId): array
    {
        try {
            /** @var RoomBlock|null $block */
            $block = $this->blockRepository->find($blockId);
            if (! $block) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_block.messages.not_found'),
                ];
            }

            if (Gate::denies('delete', $block)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_block.messages.unauthorized'),
                ];
            }

            $room = $this->roomsRepository->find($block->room_id);
            $this->blockRepository->delete($blockId);

            if ($room !== null) {
                $this->dispatchChanged($block, $room, 'deleted');
            }

            return [
                'success' => true,
                'data'    => ['id' => $blockId],
                'message' => __('room_block.messages.deleted_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('Room block delete failed', [
                'block_id' => $blockId,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('room_block.messages.delete_failed'),
            ];
        }
    }

    /**
     * Liệt kê các block thuộc partner trong khoảng ngày.
     *
     * @param array<int, int> $roomIds
     * @return array{success: bool, data: array<int, array<string, mixed>>, message: string}
     */
    public function listForPartner(array $roomIds, string $fromDate, string $toDate): array
    {
        $blocks = $this->blockRepository->listForRoomsInRange($roomIds, $fromDate, $toDate);

        $payload = $blocks->map(fn (RoomBlock $block) => [
            'id'         => (int) $block->id,
            'room_id'    => (int) $block->room_id,
            'start_date' => optional($block->start_date)->format('Y-m-d'),
            'end_date'   => optional($block->end_date)->format('Y-m-d'),
            'block_type' => (string) $block->block_type,
            'reason'     => (string) $block->reason,
            'note'       => $block->note,
            'created_by' => $block->created_by !== null ? (int) $block->created_by : null,
            'created_at' => optional($block->created_at)->toIso8601String(),
        ])->values()->all();

        return [
            'success' => true,
            'data'    => $payload,
            'message' => __('room_block.messages.retrieved_successfully'),
        ];
    }

    private function dispatchChanged(RoomBlock $block, Room $room, string $action): void
    {
        $building = $room->relationLoaded('building') ? $room->building : $room->building()->first();
        if ($building === null) {
            return;
        }

        $partnerId  = (int) $building->user_id;
        $propertyId = (int) $building->id;
        $actorId    = Auth::id();

        try {
            RoomBlockChanged::dispatch($block, $partnerId, $propertyId, $action, $actorId);
        } catch (Throwable $e) {
            Log::warning('Broadcast dispatch failed', [
                'event'    => 'room_block.changed',
                'block_id' => $block->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function normalizeDate(string $value): string
    {
        return Carbon::parse($value, 'Asia/Ho_Chi_Minh')->format('Y-m-d');
    }
}

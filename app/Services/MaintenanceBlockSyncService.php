<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Room;
use App\Models\RoomBlock;
use App\Models\RoomMaintenance;
use Carbon\Carbon;

/**
 * Đồng bộ phiếu bảo trì với room_blocks (Calendar).
 */
final class MaintenanceBlockSyncService
{
    public function __construct(
        private readonly RoomBlockService $roomBlockService,
    ) {
    }

    /**
     * @return array{success: bool, room_block_id: int|null, message?: string, code?: string, data?: mixed}
     */
    public function attachBlockOnCreate(RoomMaintenance $maintenance, Room $room): array
    {
        if (! $maintenance->block_calendar) {
            return [
                'success'       => true,
                'room_block_id' => null,
            ];
        }

        if ($maintenance->end_time === null) {
            return [
                'success' => false,
                'room_block_id' => null,
                'message' => __('room_maintenance.end_time_required_for_block'),
                'code'    => 'MAINTENANCE_VALIDATION_ERROR',
            ];
        }

        $startDate = Carbon::parse($maintenance->start_time)->format('Y-m-d');
        $endDate   = Carbon::parse($maintenance->end_time)->format('Y-m-d');

        $existing = RoomBlock::query()
            ->where('room_id', $room->id)
            ->where('block_type', RoomBlock::BLOCK_TYPE_MAINTENANCE)
            ->where('start_date', '<', $endDate)
            ->where('end_date', '>', $startDate)
            ->first();

        if ($existing !== null) {
            return [
                'success'       => true,
                'room_block_id' => (int) $existing->id,
            ];
        }

        $result = $this->roomBlockService->create([
            'room_id'    => (int) $room->id,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'block_type' => RoomBlock::BLOCK_TYPE_MAINTENANCE,
            'reason'     => (string) $maintenance->title,
            'note'       => $maintenance->description,
        ]);

        if (! $result['success']) {
            if (($result['code'] ?? null) === 'ROOM_BLOCK_CONFLICT') {
                return [
                    'success' => false,
                    'room_block_id' => null,
                    'message' => __('room_maintenance.calendar_conflict'),
                    'code'    => 'MAINTENANCE_CALENDAR_CONFLICT',
                    'data'    => $result['data'],
                ];
            }

            return [
                'success' => false,
                'room_block_id' => null,
                'message' => $result['message'],
                'code'    => 'MAINTENANCE_BLOCK_FAILED',

            ];
        }

        $block = $result['data'];

        return [
            'success'       => true,
            'room_block_id' => $block instanceof RoomBlock ? (int) $block->id : null,
        ];
    }

    public function releaseLinkedBlock(RoomMaintenance $maintenance): void
    {
        if ($maintenance->room_block_id === null) {
            return;
        }

        $block = RoomBlock::query()->find($maintenance->room_block_id);
        if ($block === null) {
            return;
        }

        if ($block->block_type !== RoomBlock::BLOCK_TYPE_MAINTENANCE) {
            return;
        }

        if ($block->reason !== $maintenance->title) {
            return;
        }

        $this->roomBlockService->delete((int) $block->id);
    }
}

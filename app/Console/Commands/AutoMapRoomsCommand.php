<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RoomStatus;
use App\Services\RoomTouristGeographyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class AutoMapRoomsCommand extends Command
{
    protected $signature = 'tourist-spots:auto-map
                            {--force : Force calculation for all public rooms, overwriting existing estimated mappings}';

    protected $description = 'Automatically calculate estimated distance and travel time from rooms to tourist spots in the same province';

    public function handle(RoomTouristGeographyService $geographyService): int
    {
        $force = (bool) $this->option('force');

        // Find all public rooms
        $rooms = DB::table('rooms')
            ->where('status', RoomStatus::PUBLIC)
            ->get(['id']);

        if ($rooms->isEmpty()) {
            $this->info('Không tìm thấy phòng công khai nào.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($rooms as $room) {
            if ($force) {
                // Delete existing estimated mappings for this room
                DB::table('room_tourist_spot_maps')
                    ->where('room_id', $room->id)
                    ->where('source_type', 'estimated')
                    ->delete();
            }

            // Check if mapping exists
            $exists = DB::table('room_tourist_spot_maps')
                ->where('room_id', $room->id)
                ->exists();

            if (!$exists) {
                $geographyService->autoMapRoomToTouristSpots((int) $room->id);
                $count++;
            }
        }

        $this->info("Đã tự động gán điểm du lịch cho {$count} phòng mới/được force reset.");

        return self::SUCCESS;
    }
}

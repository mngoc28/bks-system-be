<?php

namespace App\Services;

use App\Repositories\RoomAmenityRepository\RoomAmenityRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RoomAmenityService
{
    protected $roomAmenityRepository;

    /**
     * Constructor method.
     *
     * @param RoomAmenityRepositoryInterface $roomAmenityRepository
     */
    public function __construct(RoomAmenityRepositoryInterface $roomAmenityRepository)
    {
        $this->roomAmenityRepository = $roomAmenityRepository;
    }

    /**
     * Save room amenities
     *
     * @param int $roomId
     * @param array $amenityIds Array of amenity IDs
     * @return void
     */
    public function saveRoomAmenities($roomId, $amenityIds)
    {
        try {
            if (!empty($amenityIds) && is_array($amenityIds) && $roomId) {
                $amenityData = array_map(fn($amenityId) => [
                    'room_id' => $roomId,
                    'amenity_id' => $amenityId,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ], $amenityIds);
                $this->roomAmenityRepository->insert($amenityData);
            }
        } catch (\Throwable $e) {
            Log::error(__('room.messages.save_amenities_failed'), [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
            ]);
            throw $e;
        }
    }
}

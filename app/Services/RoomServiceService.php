<?php

namespace App\Services;

use App\Repositories\RoomServiceRepository\RoomServiceRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RoomServiceService
{
    protected $roomServiceRepository;
    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependency (RoomServiceRepositoryInterface)
     * using Dependency Injection.
     *
     * @param RoomServiceRepositoryInterface $roomServiceRepository Handles data operations for room services
     */
    public function __construct(RoomServiceRepositoryInterface $roomServiceRepository)
    {
        $this->roomServiceRepository = $roomServiceRepository;
    }

    /**
     * Save service checkbox states for a room
     *
     * @param int $roomId
     * @param array $serviceIds
     */
    public function saveServiceCheckbox($roomId, $serviceIds)
    {
        try {
            if (!empty($serviceIds) && is_array($serviceIds) && $roomId) {
                $dataFieldCheckbox = array_map(fn($service) => [
                    'room_id' => $roomId,
                    'service_id' => $service,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ], $serviceIds);
                $this->roomServiceRepository->insert($dataFieldCheckbox);
            }
        } catch (\Throwable $e) {
            Log::error('Save room service by checkbox', ['error' => $e]);
            throw $e;
        }
    }
}

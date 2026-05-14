<?php

namespace App\Services;

use App\Models\Room;
use App\Repositories\RoomMaintenanceRepository\RoomMaintenanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoomMaintenanceService
{
    public function __construct(private RoomMaintenanceRepositoryInterface $roomMaintenanceRepository)
    {
    }

    /**
     * Get list of room maintenance records with optional filters.
     *
     * @param array $filters
     * @return array
     */
    public function getList(array $filters): array
    {
        try {
            $result = $this->roomMaintenanceRepository->getList($filters);

            if ($result instanceof LengthAwarePaginator) {
                return [
                    'current_page' => $result->currentPage(),
                    'data' => $result->items(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                ];
            }

            return [
                'data' => $result,
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to retrieve room maintenance list', [
                'filters' => $filters,
                'exception' => $exception,
            ]);

            return [
                'data' => [],
            ];
        }
    }

    /**
     * Create room maintenance record.
     *
     * @param array $payload
     * @return array{success: bool, data: mixed, message: string}
     */
    public function create(array $payload): array
    {
        try {
            $room = Room::query()->find($payload['room_id']);
            if (!$room) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('room_maintenance.create_failed'),
                ];
            }

            if (empty($payload['property_id'])) {
                $payload['property_id'] = $room->property_id;
            }

            if (empty($payload['status'])) {
                $payload['status'] = 'planned';
            }

            $payload['created_by'] = Auth::id();

            $maintenance = $this->roomMaintenanceRepository->create($payload);

            return [
                'success' => true,
                'data' => $maintenance->toArray(),
                'message' => __('room_maintenance.create_success'),
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to create room maintenance', [
                'payload' => $payload,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('room_maintenance.create_failed'),
            ];
        }
    }
}

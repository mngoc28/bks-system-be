<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\RoomTouristSpotMapService;
use App\Services\RoomTouristGeographyService;
use App\Models\RoomTouristSpotMap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class PartnerRoomTouristSpotMapController extends Controller
{
    private RoomTouristSpotMapService $roomTouristSpotMapService;
    private RoomTouristGeographyService $roomTouristGeographyService;

    public function __construct(
        RoomTouristSpotMapService $roomTouristSpotMapService,
        RoomTouristGeographyService $roomTouristGeographyService
    ) {
        $this->roomTouristSpotMapService = $roomTouristSpotMapService;
        $this->roomTouristGeographyService = $roomTouristGeographyService;
    }

    /**
     * Get list of tourist spot mappings for the partner's rooms.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $partnerId = auth()->id();

            $query = RoomTouristSpotMap::query()
                ->join('rooms', 'rooms.id', '=', 'room_tourist_spot_maps.room_id')
                ->join('properties', 'properties.id', '=', 'rooms.property_id')
                ->where('properties.user_id', $partnerId)
                ->select('room_tourist_spot_maps.*')
                ->with(['room:id,title', 'touristSpot:id,name,slug,is_featured']);

            if ($request->filled('room_id')) {
                $query->where('room_tourist_spot_maps.room_id', (int) $request->input('room_id'));
            }

            if ($request->filled('tourist_spot_id')) {
                $query->where('room_tourist_spot_maps.tourist_spot_id', (int) $request->input('tourist_spot_id'));
            }

            $maps = $query->orderByDesc('room_tourist_spot_maps.is_primary')
                ->orderBy('room_tourist_spot_maps.distance_km')
                ->orderBy('room_tourist_spot_maps.travel_time_minutes')
                ->orderByDesc('room_tourist_spot_maps.id')
                ->paginate((int) $request->input('per_page', config('const.DEFAULT_PER_PAGE')));

            return $this->successResponse($maps, 'Lấy danh sách mapping điểm du lịch thành công.');
        } catch (\Throwable $throwable) {
            return $this->errorResponse('Lấy danh sách mapping điểm du lịch thất bại.', null, HttpStatus::BAD_REQUEST);
        }
    }

    /**
     * Get details of a single tourist spot mapping.
     */
    public function show(int $id): JsonResponse
    {
        $partnerId = auth()->id();

        $map = RoomTouristSpotMap::query()
            ->join('rooms', 'rooms.id', '=', 'room_tourist_spot_maps.room_id')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('properties.user_id', $partnerId)
            ->where('room_tourist_spot_maps.id', $id)
            ->select('room_tourist_spot_maps.*')
            ->with(['room:id,title', 'touristSpot:id,name,slug,is_featured'])
            ->first();

        if (!$map) {
            return $this->errorResponse('Không tìm thấy mapping hoặc bạn không có quyền truy cập.', null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($map, 'Lấy chi tiết mapping thành công.');
    }

    /**
     * Map a partner's room to a tourist spot.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'tourist_spot_id' => [
                'required',
                'integer',
                'exists:tourist_spots,id',
                function ($attribute, $value, $fail) use ($request) {
                    $roomId = $request->input('room_id');
                    if ($roomId) {
                        $exists = DB::table('room_tourist_spot_maps')
                            ->where('room_id', $roomId)
                            ->where('tourist_spot_id', $value)
                            ->exists();
                        if ($exists) {
                            $fail('Phòng này đã được gán địa điểm du lịch này rồi.');
                        }
                    }
                }
            ],
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'travel_time_minutes' => ['required', 'integer', 'min:1'],
            'is_primary' => ['nullable', 'boolean'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
            'apply_to_all_rooms' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $roomId = (int) $request->input('room_id');
        $touristSpotId = (int) $request->input('tourist_spot_id');
        $partnerId = auth()->id();

        // 1. Verify that the room belongs to a property owned by the partner
        $ownsRoom = DB::table('rooms')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('rooms.id', $roomId)
            ->where('properties.user_id', $partnerId)
            ->exists();

        if (!$ownsRoom) {
            return $this->errorResponse('Phòng không thuộc sở hữu của bạn hoặc không tồn tại.', null, HttpStatus::FORBIDDEN);
        }

        // 2. Verify geographic consistency (must be in the same province)
        if (!$this->roomTouristGeographyService->roomMatchesSpotProvince($roomId, $touristSpotId)) {
            return $this->errorResponse('Phòng và địa điểm du lịch phải thuộc cùng một Tỉnh/Thành phố.', null, HttpStatus::BAD_REQUEST);
        }

        $result = $this->roomTouristSpotMapService->store($request->only([
            'room_id', 'tourist_spot_id', 'distance_km', 'travel_time_minutes',
            'priority_order', 'is_primary', 'note', 'apply_to_all_rooms'
        ]));

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    /**
     * Update an existing mapping.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'distance_km' => ['nullable', 'numeric', 'min:0'],
            'travel_time_minutes' => ['nullable', 'integer', 'min:1'],
            'is_primary' => ['nullable', 'boolean'],
            'priority_order' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string'],
            'apply_to_all_rooms' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $partnerId = auth()->id();

        // Verify mapping belongs to partner's room
        $map = RoomTouristSpotMap::query()
            ->join('rooms', 'rooms.id', '=', 'room_tourist_spot_maps.room_id')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('properties.user_id', $partnerId)
            ->where('room_tourist_spot_maps.id', $id)
            ->select('room_tourist_spot_maps.*')
            ->first();

        if (!$map) {
            return $this->errorResponse('Không tìm thấy mapping hoặc bạn không có quyền truy cập.', null, HttpStatus::NOT_FOUND);
        }

        $result = $this->roomTouristSpotMapService->update($id, $request->only([
            'distance_km', 'travel_time_minutes', 'priority_order', 'is_primary', 'note', 'apply_to_all_rooms'
        ]));

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Delete an existing mapping.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $partnerId = auth()->id();

        // Verify mapping belongs to partner's room
        $exists = RoomTouristSpotMap::query()
            ->join('rooms', 'rooms.id', '=', 'room_tourist_spot_maps.room_id')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('properties.user_id', $partnerId)
            ->where('room_tourist_spot_maps.id', $id)
            ->exists();

        if (!$exists) {
            return $this->errorResponse('Không tìm thấy mapping hoặc bạn không có quyền truy cập.', null, HttpStatus::NOT_FOUND);
        }

        $applyToAllRooms = $request->boolean('apply_to_all_rooms');
        $result = $this->roomTouristSpotMapService->destroy($id, ['apply_to_all_rooms' => $applyToAllRooms]);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }
}

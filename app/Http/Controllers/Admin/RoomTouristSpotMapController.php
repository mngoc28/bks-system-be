<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\RoomTouristSpotMapValidation;
use App\Services\RoomTouristSpotMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoomTouristSpotMapController extends Controller
{
    private RoomTouristSpotMapService $roomTouristSpotMapService;
    private RoomTouristSpotMapValidation $roomTouristSpotMapValidation;

    public function __construct()
    {
        $this->roomTouristSpotMapService = app(RoomTouristSpotMapService::class);
        $this->roomTouristSpotMapValidation = app(RoomTouristSpotMapValidation::class);
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->roomTouristSpotMapValidation->indexValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->roomTouristSpotMapService->index($request);
        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->roomTouristSpotMapService->detail($id);
        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->roomTouristSpotMapValidation->storeValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->roomTouristSpotMapService->store($request->only([
            'room_id', 'tourist_spot_id', 'distance_km', 'travel_time_minutes',
            'priority_order', 'is_primary', 'source_type', 'note', 'apply_to_all_rooms',
        ]));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->roomTouristSpotMapValidation->updateValidation($request, $id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->roomTouristSpotMapService->update($id, $request->only([
            'room_id', 'tourist_spot_id', 'distance_km', 'travel_time_minutes',
            'priority_order', 'is_primary', 'source_type', 'note', 'apply_to_all_rooms',
        ]));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $applyToAllRooms = $request->boolean('apply_to_all_rooms');
        $result = $this->roomTouristSpotMapService->destroy($id, ['apply_to_all_rooms' => $applyToAllRooms]);
        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse(null, $result['message']);
    }
}

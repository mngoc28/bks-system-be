<?php

declare(strict_types=1);

namespace App\Http\Controllers\EU;

use App\Http\Controllers\Controller;
use App\Enums\HttpStatus;
use App\Http\Validations\RoomsValidation;
use App\Services\RoomsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoomController extends Controller
{
    /**
     * Service layer that handles business logic for rooms.
     * Validation layer that handles request data validation for rooms.
     */
    protected RoomsService $roomsService;
    protected RoomsValidation $roomsValidation;

    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependencies (RoomsService and RoomsValidation)
     * using Dependency Injection.
     *
     * @param RoomsService $roomsService       Handles business logic for rooms
     * @param RoomsValidation $roomsValidation Validates input data for rooms
     */
    public function __construct(RoomsService $roomsService, RoomsValidation $roomsValidation)
    {
        $this->roomsService    = $roomsService;
        $this->roomsValidation = $roomsValidation;
    }

    /**
     * Get room list with filters
     *
     * @param Request $request Incoming HTTP request with query parameters
     * @return JsonResponse JSON response containing room list or error message
     */
    public function roomList(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->searchRoomValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomsService->handleRoomList($request);

        if (!$result["success"]) {
            return $this->errorResponse(
                $result["message"],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result["data"],
            $result["message"]
        );
    }

    /**
     * Get public room detail by ID
     *
     * @param int $id Room ID
     * @return JsonResponse JSON response containing room details or error message
     */
    public function publicRoomDetail($id): JsonResponse
    {
        $result = $this->roomsService->handlePublicRoomDetail($id);
        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::NOT_FOUND
            );
        }
        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }
}

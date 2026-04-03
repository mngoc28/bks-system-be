<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\RoomsValidation;
use App\Services\RoomsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerRoomController extends Controller
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
     * @param RoomsService $roomsService       Handles business logic for rooms
     * @param RoomsValidation $roomsValidation Validates input data for rooms
     */
    public function __construct(RoomsService $roomsService, RoomsValidation $roomsValidation)
    {
        $this->roomsService    = $roomsService;
        $this->roomsValidation = $roomsValidation;
    }

    /**
     * Handle the incoming request to search for rooms or get all rooms for partner.
     *
     * @param Request $request The incoming HTTP request containing search parameters
     * @return JsonResponse A JSON response containing the search results or errors
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->searchRoomValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->roomsService->handleGetAllRoomsForPartner($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Handle the incoming request to get a room by its ID for partner.
     *
     * @param int $id The ID of the room to retrieve
     * @return JsonResponse A JSON response containing the room details or errors
     */
    public function show($id): JsonResponse
    {
        $result = $this->roomsService->handleGetRoomDetailForPartner((int)$id);
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

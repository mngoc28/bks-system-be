<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\RoomsValidation;
use App\Services\RoomsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RoomsController extends Controller
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
     * Handle the incoming request to search for rooms or get all rooms.
     *
     * This method validates the request parameters and then calls the service layer
     * to perform the search or retrieval of rooms. It returns a JSON response with
     * the results or validation errors.
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
        $result = $this->roomsService->getAllOrSearchRooms($request);

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
     * Handle the incoming request to get a room by its ID.
     *
     * This method validates the room ID and then calls the service layer
     * to retrieve the room details. It returns a JSON response with
     * the room details or validation errors.
     *
     * @param int $id The ID of the room to retrieve
     * @return JsonResponse A JSON response containing the room details or errors
     */
    public function show($id): JsonResponse
    {
        $result = $this->roomsService->getRoomById($id);
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

    /**
     * Handle the incoming request to create a new room.
     *
     * This method validates the request data and then calls the service layer
     * to create a new room. It returns a JSON response with the created room details
     * or validation errors.
     *
     * @param Request $request The incoming HTTP request containing room data
     * @return JsonResponse A JSON response containing the created room details or errors
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->createRoomValidation($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomsService->createRoom($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->createdResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Handle the incoming request to update an existing room.
     *
     * This method validates the request data and room ID, then calls the service layer
     * to update the room. It returns a JSON response with the updated room details
     * or validation errors.
     *
     * @param Request $request The incoming HTTP request containing updated room data
     * @param int $id The ID of the room to update
     * @return JsonResponse A JSON response containing the updated room details or errors
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = $this->roomsValidation->updateRoomValidation($request, $id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomsService->updateRoom($request, $id);
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
     * Handle the incoming request to delete a room by its ID.
     *
     * This method validates the room ID and then calls the service layer
     * to delete the room. It returns a JSON response indicating success or failure.
     *
     * @param int $id The ID of the room to delete
     * @return JsonResponse A JSON response indicating success or failure of the deletion
     */
    public function destroy($id): JsonResponse
    {
        $validator = $this->roomsValidation->deleteRoomValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->roomsService->deleteRoom($id);
        if (! $result['success']) {
            $statusCode = $result['data'] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::BAD_REQUEST;
            return $this->errorResponse(
                $result['message'],
                null,
                $statusCode
            );
        }
        return $this->successResponse(
            null,
            $result['message']
        );
    }

    /**
     * Room titles/numbers for a property (admin).
     *
     * @param int|string $propertyId
     * @return JsonResponse
     */
    public function getRoomNamesByPropertyId($propertyId): JsonResponse
    {
        $result = $this->roomsService->getRoomNamesByPropertyId($propertyId);
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

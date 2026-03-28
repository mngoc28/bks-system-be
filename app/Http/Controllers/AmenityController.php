<?php

namespace App\Http\Controllers;

use App\Services\AmenityService;
use App\Enums\HttpStatus;
use Illuminate\Http\JsonResponse;
use App\Http\Validations\AmenityValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class AmenityController extends Controller
{
    protected AmenityService $amenityService;
    protected AmenityValidation $amenityValidation;
    /**
     * Summary of __construct
     * @param AmenityService $amenityService
     */
    public function __construct(AmenityService $amenityService, AmenityValidation $amenityValidation)
    {
        $this->amenityService = $amenityService;
        $this->amenityValidation = $amenityValidation;
    }

    /**
     * Get all amenities
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->amenityValidation->indexValidation($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('amenity.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->amenityService->getAllAmenities($request);
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
     * Get all amenities without pagination
     *
     * @return JsonResponse
     */
    public function getAllAmenities(): JsonResponse
    {
        $result = $this->amenityService->getAllAmenitiesWithoutPagination();
        if (!$result['success']) {
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
     * Get amenity by ID
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $validator = $this->amenityValidation->getByIdValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->amenityService->getAmenityById($id);
        if (! $result) {
            return $this->errorResponse(
                __('amenity.messages.fetch_error'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result,
            __('amenity.messages.fetch_success')
        );
    }

    /**
     * Store a new amenity
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->merge(['created_by' => Auth::user()->id]);
        $validator = $this->amenityValidation->createValidation($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('amenity.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->amenityService->createAmenity($request);
        if (! $result) {
            return $this->errorResponse(
                __('amenity.messages.create_error'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result,
            __('amenity.messages.create_success')
        );
    }

    /**
     * Update an existing amenity
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $request->merge(['updated_by' => Auth::user()->id]);
        $validator = $this->amenityValidation->updateValidation($id, $request);
        if ($validator->fails()) {
            return $this->errorResponse(
                __('amenity.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->amenityService->updateAmenity($id, $request);
        if (! $result) {
            return $this->errorResponse(
                __('amenity.messages.update_error'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result,
            __('amenity.messages.update_success')
        );
    }

    /**
     * Delete amenity by ID
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $validator = $this->amenityValidation->getByIdValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->amenityService->deleteAmenity($id);
        if (! $result) {
            return $this->errorResponse(
                __('amenity.messages.delete_error'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            null,
            __('amenity.messages.delete_success')
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\PropertiesValidation;
use App\Services\PropertiesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PropertiesController extends Controller
{
    protected PropertiesService $propertiesService;
    protected PropertiesValidation $propertiesValidation;

    public function __construct(
        PropertiesService $propertiesService,
        PropertiesValidation $propertiesValidation
    ) {
        $this->propertiesService     = $propertiesService;
        $this->propertiesValidation = $propertiesValidation;
    }

    /**
     * List/search properties (paginated).
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->propertiesValidation->searchPropertyValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->propertiesService->getAllOrSearchProperties($request);

        if (!$result["success"]) {
            return $this->errorResponse($result["message"], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get property by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->propertiesValidation->detailPropertyValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->propertiesService->getPropertyById($id);

        if (!$result["success"]) {
            $statusCode = $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse($result["message"], null, $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Create new property
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // For partner portal, we automatically assign the user_id to the authenticated user if not provided
        if (!$request->has('user_id') || $request->user_id == 0) {
            $request->merge(['user_id' => auth()->id()]);
        }

        $validator = $this->propertiesValidation->createPropertyValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->propertiesService->createProperty($request->all());

        if (!$result["success"]) {
            return $this->errorResponse($result["message"], null, HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result["data"], $result["message"]);
    }

    /**
     * Update property
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->propertiesValidation->updatePropertyValidation($request, $id);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->propertiesService->updateProperty($id, $request->all());

        if (!$result["success"]) {
            $statusCode =
                $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse($result["message"], null, $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Delete property
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->propertiesValidation->deletePropertyValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->propertiesService->deleteProperty($id);

        if (!$result["success"]) {
            $statusCode =
                $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::BAD_REQUEST;
            return $this->errorResponse($result["message"], null, $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get bookings by property ID
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookingsByProperty(int $id, Request $request): JsonResponse
    {
        $validator = $this->propertiesValidation->getBookingsByPropertyValidation($id, $request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->propertiesService->getBookingsByProperty($id, $request);

        if (!$result["success"]) {
            $statusCode =
                $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::BAD_REQUEST;
            return $this->errorResponse($result["message"], null, $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get all property types
     *
     * @return JsonResponse
     */
    public function getAllPropertyTypes(): JsonResponse
    {
        $result = $this->propertiesService->getAllPropertyTypes();

        if (!$result["success"]) {
            return $this->errorResponse(
                $result["message"],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get all property names (no pagination)
     *
     * @return JsonResponse
     */
    public function getAllPropertyNames(): JsonResponse
    {
        $result = $this->propertiesService->getAllPropertyNames();

        if (!$result["success"]) {
            return $this->errorResponse(
                $result["message"],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse($result["data"], $result["message"]);
    }
}

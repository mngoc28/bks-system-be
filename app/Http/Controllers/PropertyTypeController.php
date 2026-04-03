<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\PropertyTypeValidation;
use App\Services\PropertyTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    public function __construct(
        private PropertyTypeService $propertyTypeService,
        private PropertyTypeValidation $propertyTypeValidation
    ) {
    }

    /**
     * Create new property type.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->propertyTypeValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('property_type.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->propertyTypeService->store($validator->validated());

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message'],
            HttpStatus::CREATED
        );
    }

    /**
     * Retrieve property types list.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->propertyTypeValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('property_type.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->propertyTypeService->list($validator->validated());

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
     * Retrieve property type detail.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->propertyTypeValidation->detailValidation($id);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('property_type.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->propertyTypeService->detail($id);

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
     * Update property type detail.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $validator = $this->propertyTypeValidation->updateValidation($id, $request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('property_type.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->propertyTypeService->update($id, $validator->validated());

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
     * Update property type status.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $validator = $this->propertyTypeValidation->updateStatusValidation($id, $request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('property_type.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->propertyTypeService->updateStatus($id, $validator->validated());

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
}

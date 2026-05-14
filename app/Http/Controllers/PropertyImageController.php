<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\PropertyImageValidation;
use App\Services\PropertyImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PropertyImageController extends Controller
{
    protected PropertyImageService $propertyImageService;
    protected PropertyImageValidation $propertyImageValidation;

    public function __construct(
        PropertyImageService $propertyImageService,
        PropertyImageValidation $propertyImageValidation
    ) {
        $this->propertyImageService     = $propertyImageService;
        $this->propertyImageValidation = $propertyImageValidation;
    }

    /**
     * Get images by property ID
     *
     * @param int $propertyId
     * @return JsonResponse
     */
    public function getByPropertyId(int $propertyId): JsonResponse
    {
        $validator = $this->propertyImageValidation->getByPropertyIdValidation($propertyId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->propertyImageService->getByPropertyId($propertyId);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Show property image by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->propertyImageValidation->showValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->propertyImageService->show($id);

        if (!$result) {
            return $this->errorResponse(__('property_image.messages.find_failed'), null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('property_image.messages.found_successfully'));
    }

    /**
     * Store new property image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->propertyImageValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->propertyImageService->store($request->all());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Update property image
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->propertyImageValidation->updateValidation($request, $id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->propertyImageService->update($id, $request->all());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Destroy property image
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->propertyImageValidation->destroyValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->propertyImageService->destroy($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Sort property images
     *
     * @param Request $request
     * @param int $propertyId
     * @return JsonResponse
     */
    public function sort(Request $request, int $propertyId): JsonResponse
    {
        $validator = $this->propertyImageValidation->sortValidation($request, $propertyId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->propertyImageService->sort($request->all(), $propertyId);

        if (!$result) {
            return $this->errorResponse(__('property_image.messages.sort_failed'), null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result, __('property_image.messages.sort_successfully'));
    }
}

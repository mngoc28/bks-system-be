<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\BuildingImageValidation;
use App\Services\BuildingImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BuildingImageController extends Controller
{
    /**
     * BuildingImage service and validation instance
     */
    protected BuildingImageService $buildingImageService;
    protected BuildingImageValidation $buildingImageValidation;

    /**
     * Constructor
     *
     * @param BuildingImageService $buildingImageService
     * @param BuildingImageValidation $buildingImageValidation
     */
    public function __construct(
        BuildingImageService $buildingImageService,
        BuildingImageValidation $buildingImageValidation
    ) {
        $this->buildingImageService = $buildingImageService;
        $this->buildingImageValidation = $buildingImageValidation;
    }

    /**
     * Get images by building ID
     *
     * @param Request $request
     * @param int $buildingId
     * @return JsonResponse
     */
    public function getByBuildingId(int $buildingId): JsonResponse
    {
        $validator = $this->buildingImageValidation->getByBuildingIdValidation($buildingId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->buildingImageService->getByBuildingId($buildingId);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Show building image by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->buildingImageValidation->showValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->buildingImageService->show($id);

        if (!$result) {
            return $this->errorResponse(__('building_image.messages.find_failed'), null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result, __('building_image.messages.found_successfully'));
    }

    /**
     * Store new building image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->buildingImageValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->buildingImageService->store($request->all());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Update building image
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->buildingImageValidation->updateValidation($request, $id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->buildingImageService->update($id, $request->all());

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Destroy building image
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->buildingImageValidation->destroyValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->buildingImageService->destroy($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Sort building images
     *
     * @param Request $request
     * @param int $buildingId
     * @return JsonResponse
     */
    public function sort(Request $request, int $buildingId): JsonResponse
    {
        $validator = $this->buildingImageValidation->sortValidation($request, $buildingId);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->buildingImageService->sort($request->all(), $buildingId);

        if (!$result) {
            return $this->errorResponse(__('building_image.messages.sort_failed'), null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result, __('building_image.messages.sort_successfully'));
    }
}

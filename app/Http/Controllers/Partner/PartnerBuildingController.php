<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\BuildingsValidation;
use App\Services\BuildingsServices;
use App\Services\BuildingImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerBuildingController extends Controller
{
    /**
     * Building services and validation instance
     */
    protected BuildingsServices $buildingServices;
    protected BuildingsValidation $buildingsValidation;
    protected BuildingImageService $buildingImageService;

    /**
     * Constructor
     *
     * @param BuildingsServices $buildingServices
     * @param BuildingsValidation $buildingsValidation
     * @param BuildingImageService $buildingImageService
     */
    public function __construct(
        BuildingsServices $buildingServices,
        BuildingsValidation $buildingsValidation,
        BuildingImageService $buildingImageService
    ) {
        $this->buildingServices = $buildingServices;
        $this->buildingsValidation = $buildingsValidation;
        $this->buildingImageService = $buildingImageService;
    }

    /**
     * Store new building
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->buildingsValidation->createBuildingValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->buildingServices->createBuilding($request->all());
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->createdResponse($result['data'], $result['message']);
    }

    /**
     * Update building
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = $this->buildingsValidation->updateBuildingValidation($request, (int)$id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->buildingServices->updateBuilding((int)$id, $request->all());
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Delete building
     */
    public function destroy($id): JsonResponse
    {
        $validator = $this->buildingsValidation->deleteBuildingValidation((int)$id);
        if ($validator->fails()) {
             return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->buildingServices->deleteBuilding((int)$id);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse(null, $result['message']);
    }

    /**
     * Get all buildings for partner
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->buildingsValidation->searchBuildingValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->buildingServices->handleGetAllBuildingsForPartner($request);

        if (!$result["success"]) {
            return $this->errorResponse($result["message"], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get building detail for partner
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->buildingsValidation->detailBuildingValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->buildingServices->handleGetBuildingDetailForPartner($id);

        if (!$result["success"]) {
            $statusCode = $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse($result["message"], null, $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get building names for partner
     *
     * @return JsonResponse
     */
    public function getBuildingNames(): JsonResponse
    {
        $result = $this->buildingServices->handleGetBuildingNamesForPartner();

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
     * Get building images
     */
    public function getImages($id): JsonResponse
    {
        $result = $this->buildingImageService->getByBuildingId((int)$id);
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Add building image
     */
    public function storeImages(Request $request, $id): JsonResponse
    {
        $request->validate([
            'image_url' => 'required|string',
            'id_image_cloudinary' => 'required|string',
            'image_type' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['building_id'] = (int)$id;
        $data['image_type'] = $data['image_type'] ?? 1;

        $result = $this->buildingImageService->store($data);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Delete building image
     */
    public function deleteImage($id, $imageId): JsonResponse
    {
        $result = $this->buildingImageService->destroy((int)$imageId);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse(null, $result['message']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\PropertiesValidation;
use App\Services\PropertiesService;
use App\Services\PartnerPropertyRoomPreviewService;
use App\Services\PropertyImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerPropertyController extends Controller
{
    protected PropertiesService $propertiesService;
    protected PropertiesValidation $propertiesValidation;
    protected PropertyImageService $propertyImageService;
    protected PartnerPropertyRoomPreviewService $roomPreviewService;

    public function __construct(
        PropertiesService $propertiesService,
        PropertiesValidation $propertiesValidation,
        PropertyImageService $propertyImageService,
        PartnerPropertyRoomPreviewService $roomPreviewService
    ) {
        $this->propertiesService     = $propertiesService;
        $this->propertiesValidation = $propertiesValidation;
        $this->propertyImageService  = $propertyImageService;
        $this->roomPreviewService    = $roomPreviewService;
    }

    /**
     * Store new property (partner portal).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->propertiesValidation->createPropertyValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->propertiesService->createProperty($request->all());
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->createdResponse($result['data'], $result['message']);
    }

    /**
     * Update property (partner portal).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = $this->propertiesValidation->updatePropertyValidation($request, (int)$id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->propertiesService->updateProperty((int)$id, $request->all());
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Delete property (partner portal).
     */
    public function destroy($id): JsonResponse
    {
        $validator = $this->propertiesValidation->deletePropertyValidation((int)$id);
        if ($validator->fails()) {
             return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->propertiesService->deleteProperty((int)$id);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse(null, $result['message']);
    }

    /**
     * List/search properties for partner.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->propertiesValidation->searchPropertyValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->propertiesService->handleGetAllPropertiesForPartner($request);

        if (!$result["success"]) {
            return $this->errorResponse($result["message"], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Room preview for a single partner property (max 20 rooms).
     */
    public function roomPreview(Request $request, int $id): JsonResponse
    {
        $validator = $this->propertiesValidation->roomPreviewValidation($request, $id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $limit  = min(max((int) $request->input('limit', 6), 1), 20);
        $result = $this->roomPreviewService->getPreview($id, $limit);

        if (! $result['success']) {
            $statusCode = $result['data'] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::BAD_REQUEST;

            return $this->errorResponse($result['message'], null, $statusCode);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get property detail for partner
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

        $result = $this->propertiesService->handleGetPropertyDetailForPartner($id);

        if (!$result["success"]) {
            $statusCode = $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse($result["message"], null, $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get property display names for partner
     *
     * @return JsonResponse
     */
    public function getPropertyNames(): JsonResponse
    {
        $result = $this->propertiesService->handleGetPropertyNamesForPartner();

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
     * Get property images
     */
    public function getImages($id): JsonResponse
    {
        $result = $this->propertyImageService->getByPropertyId((int)$id);
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Add property image
     */
    public function storeImages(Request $request, $id): JsonResponse
    {
        $request->validate([
            'image_url' => 'required|string',
            'id_image_cloudinary' => 'required|string',
            'image_type' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['property_id'] = (int)$id;
        $data['image_type'] = $data['image_type'] ?? 1;

        $result = $this->propertyImageService->store($data);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Delete property image
     */
    public function deleteImage($id, $imageId): JsonResponse
    {
        $result = $this->propertyImageService->destroy((int)$imageId);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse(null, $result['message']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\RoomImageValidation;
use App\Http\Validations\CloudinaryValidation;
use App\Services\RoomImageService;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

final class RoomImageController extends Controller
{
    /**
     * RoomImage service and validation instance
     */
    protected RoomImageService $roomImageService;
    protected RoomImageValidation $roomImageValidation;
    protected CloudinaryValidation $cloudinaryValidation;
    protected CloudinaryService $cloudinaryService;

    /**
     * Constructor
     *
     * @param RoomImageService $roomImageService
     * @param RoomImageValidation $roomImageValidation
     * @param CloudinaryService $cloudinaryService
     * @param CloudinaryValidation $cloudinaryValidation
     */
    public function __construct(
        RoomImageService $roomImageService,
        RoomImageValidation $roomImageValidation,
        CloudinaryValidation $cloudinaryValidation,
        CloudinaryService $cloudinaryService
    ) {
        $this->roomImageService = $roomImageService;
        $this->roomImageValidation = $roomImageValidation;
        $this->cloudinaryValidation = $cloudinaryValidation;
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Get images by room ID
     * @param int $roomId
     * @return JsonResponse
     */
    public function getByRoomId(int $roomId): JsonResponse
    {
        $validator = $this->roomImageValidation->getByRoomIdValidation($roomId);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomImageService->getByRoomId($roomId);

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
     * Show room image by ID
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->roomImageValidation->showValidation($id);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomImageService->show($id);

        if (!$result) {
            return $this->errorResponse(
                __('room_image.messages.find_failed'),
                null,
                HttpStatus::NOT_FOUND
            );
        }

        return $this->successResponse(
            $result,
            __('room_image.messages.found_successfully')
        );
    }

    /**
     * Store new room image
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $upLoadValidator = $this->cloudinaryValidation->uploadMultipleImagesValidation($request);

        if ($upLoadValidator->fails()) {
            return $this->validateError(
                $upLoadValidator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        // Upload images to Cloudinary
        $files = $request->file('images');
        if (!is_array($files)) {
            $files = [$files];
        }
        $uploadResult = $this->cloudinaryService->uploadMultipleImages(
            $files,
            'rooms/' . $request->input('room_id')
        );

        if (!$uploadResult['success']) {
            return $this->errorResponse(
                $uploadResult['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        $uploadedImages = $uploadResult['images'];
        $savedImages = [];
        $errors = [];

        foreach ($uploadedImages as $index => $imageData) {
            $data = [
                'room_id' => $request->input('room_id'),
                'image_url' => $imageData['url'],
                'id_image_cloudinary' => $imageData['public_id'],
                'image_type' => $request->input('image_type', 0),
            ];

            $result = $this->roomImageService->store($data);

            if ($result['success']) {
                $savedImages[] = $result['data'];
            } else {
                $errors[] = __('cloudinary.messages.upload_error') . " " . ($index + 1) . ": " . $result['message'];
            }
        }

        if (!empty($errors)) {
            // Rollback: Delete uploaded images if DB save failed
            $this->cloudinaryService->deleteMultipleImages(array_column($uploadedImages, 'public_id'));
            return $this->errorResponse(
                __('cloudinary.messages.upload_multiple_failed') . ': ' . implode(', ', $errors),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $savedImages,
            __('cloudinary.messages.upload_multiple_success')
        );
    }

    /**
     * Update room image type
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateType(Request $request): JsonResponse
    {
        $updates = $request->input('updates', []);
        if (!is_array($updates) || empty($updates)) {
            return $this->errorResponse(
                __('room_image.validation.updates.array_required'),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $validator = $this->roomImageValidation->updateTypeValidation($request, $updates);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomImageService->updateType($updates);

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
     * Update room image sort order
     */
    public function updateSort(int $roomId, int $imageIdA, int $imageIdB): JsonResponse
    {
        $validator = $this->roomImageValidation->updateSortValidation(
            $roomId,
            $imageIdA,
            $imageIdB
        );

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomImageService->updateSort($roomId, $imageIdA, $imageIdB);

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
     * Destroy room image
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids)) {
            $ids = [$request->input('id')];
        }

        $validator = $this->roomImageValidation->destroyValidation($ids);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomImageService->destroy($ids);

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
}

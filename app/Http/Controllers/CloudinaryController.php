<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\CloudinaryValidation;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CloudinaryController extends Controller
{
    /**
     * Cloudinary service and validation instance
     */
    protected CloudinaryService $cloudinaryService;
    protected CloudinaryValidation $cloudinaryValidation;

    /**
     * Constructor
     *
     * @param CloudinaryService $cloudinaryService
     * @param CloudinaryValidation $cloudinaryValidation
     */
    public function __construct(
        CloudinaryService $cloudinaryService,
        CloudinaryValidation $cloudinaryValidation
    ) {
        $this->cloudinaryService = $cloudinaryService;
        $this->cloudinaryValidation = $cloudinaryValidation;
    }

    /**
     * Upload single image to Cloudinary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = $this->cloudinaryValidation->uploadImageValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->cloudinaryService->uploadImage(
            $request->file('image'),
            $request->input('folder')
        );

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            [
                'url' => $result['url'],
                'public_id' => $result['public_id'],
            ],
            $result['message']
        );
    }

    /**
     * Upload multiple images to Cloudinary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadMultipleImages(Request $request): JsonResponse
    {
        $validator = $this->cloudinaryValidation->uploadMultipleImagesValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->cloudinaryService->uploadMultipleImages(
            $request->file('images'),
            $request->input('folder')
        );

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                ['errors' => $result['errors'] ?? []],
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            [
                'images' => $result['images'],
                'total' => count($result['images']),
            ],
            $result['message']
        );
    }

    /**
     * Delete image from Cloudinary
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $validator = $this->cloudinaryValidation->deleteImageValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->cloudinaryService->deleteImage($request->input('public_id'));

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(null, $result['message']);
    }
}

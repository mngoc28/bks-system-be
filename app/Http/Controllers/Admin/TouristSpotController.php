<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\TouristSpotValidation;
use App\Services\TouristSpotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TouristSpotController extends Controller
{
    public function __construct(
        private readonly TouristSpotService $touristSpotService,
        private readonly TouristSpotValidation $touristSpotValidation
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validator = $this->touristSpotValidation->indexValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->touristSpotService->index($request);
        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->touristSpotService->detail($id);
        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->touristSpotValidation->storeValidation($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->touristSpotService->store($request->only([
            'name', 'slug', 'category', 'region_label', 'is_featured', 'sort_order', 'is_active',
        ]));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->touristSpotValidation->updateValidation($request, $id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->touristSpotService->update($id, $request->only([
            'name', 'slug', 'category', 'region_label', 'is_featured', 'sort_order', 'is_active',
        ]));

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    public function destroy(int $id): JsonResponse
    {
        $result = $this->touristSpotService->destroy($id);
        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse(null, $result['message']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\ProvincesValidation;
use App\Services\ProvincesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProvincesController extends Controller
{
    protected ProvincesService $provincesService;
    protected ProvincesValidation $provincesValidation;

    /**
     * Constructor
     * @param ProvincesService $provincesService
     * @param ProvincesValidation $provincesValidation
     */
    public function __construct(ProvincesService $provincesService, ProvincesValidation $provincesValidation)
    {
        $this->provincesService = $provincesService;
        $this->provincesValidation = $provincesValidation;
    }

    /**
     * Get a Province by ID
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $results = $this->provincesService->getProvinceById($id);
        if (!$results) {
            return $this->errorResponse(
                __('province.messages.not_found'),
                null,
                HttpStatus::NOT_FOUND
            );
        }

        return $this->successResponse(
            $results,
            __('province.messages.show_success')
        );
    }

    /**
     * Get all Provinces
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $results = $this->provincesService->listProvinces($request);
        if (!$results) {
            return $this->errorResponse(
                __('province.messages.search_error'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $results,
            __('province.messages.search_success')
        );
    }

    /**
     * Get all provinces types
     * @return JsonResponse
     */
    public function getAllProvincesTypes(): JsonResponse
    {
        $results = $this->provincesService->getAllProvincesTypes();
        if (!$results || $results === null) {
            return $this->errorResponse(
                __('province.messages.get_all_provinces_types_failed'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }
        return $this->successResponse($results, __('province.messages.get_all_provinces_types_success'));
    }

    /**
     * Update a province's details
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->provincesValidation->updateProvinceValidation($request, $id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $results = $this->provincesService->updateProvince($id, $request->only(['name', 'name_en', 'image']));
        if (!$results['success']) {
            return $this->errorResponse($results['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($results['data'], $results['message']);
    }
}

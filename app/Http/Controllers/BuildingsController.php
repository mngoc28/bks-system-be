<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\BuildingsValidation;
use App\Services\BuildingsServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BuildingsController extends Controller
{
    /**
     * Building services and validation instance
     */
    protected BuildingsServices $buildingServices;
    protected BuildingsValidation $buildingsValidation;

    /**
     * Constructor
     *
     * @param BuildingsServices $buildingServices
     * @param BuildingsValidation $buildingsValidation
     */
    public function __construct(
        BuildingsServices $buildingServices,
        BuildingsValidation $buildingsValidation
    ) {
        $this->buildingServices = $buildingServices;
        $this->buildingsValidation = $buildingsValidation;
    }

    /**
     * Get all buildings
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->buildingsValidation->searchBuildingValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }
        $result = $this->buildingServices->getAllOrSearchBuildings($request);

        if (!$result["success"]) {
            return $this->errorResponse($result["message"], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get building by ID
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

        $result = $this->buildingServices->getBuildingById($id);

        if (!$result["success"]) {
            $statusCode = $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse($result["message"], null, $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Create new building
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->buildingsValidation->createBuildingValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->buildingServices->createBuilding($request->all());

        if (!$result["success"]) {
            return $this->errorResponse($result["message"], null, HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result["data"], $result["message"]);
    }

    /**
     * Update building
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->buildingsValidation->updateBuildingValidation($request, $id);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->buildingServices->updateBuilding($id, $request->all());

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
     * Delete building
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->buildingsValidation->deleteBuildingValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->buildingServices->deleteBuilding($id);

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
     * Get bookings by building ID
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookingsByBuilding(int $id, Request $request): JsonResponse
    {
        $validator = $this->buildingsValidation->getBookingsByBuildingValidation($id, $request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->buildingServices->getBookingsByBuilding($id, $request);

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
     * Get all buildings types
     *
     * @return JsonResponse
     */
    public function getAllBuildingsTypes(): JsonResponse
    {
        $result = $this->buildingServices->getAllBuildingsTypes();

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
     * Get all buildings without pagination
     *
     * @return JsonResponse
     */
    public function getAllBuildingNames(): JsonResponse
    {
        $result = $this->buildingServices->getAllBuildingNames();

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

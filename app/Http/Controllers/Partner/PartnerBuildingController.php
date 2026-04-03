<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\BuildingsValidation;
use App\Services\BuildingsServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerBuildingController extends Controller
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
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\PartnerInforValidation;
use App\Services\PartnerInforServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerInforController extends Controller
{
    /**
     * partnerInfor services and validation instance
     */
    protected PartnerInforServices $partnerInforServices;
    protected PartnerInforValidation $partnerInforValidation;

    /**
     * Contruct method
     * @param PartnerInforServices $partnerInforServices
     * @param PartnerInforValidation $partnerInforValidation
     */
    public function __construct(
        PartnerInforServices $partnerInforServices,
        PartnerInforValidation $partnerInforValidation,
    ) {
        $this->partnerInforServices = $partnerInforServices;
        $this->partnerInforValidation = $partnerInforValidation;
    }

    /**
     * Get list partner information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->partnerInforValidation->searchPartnerInforValidation($request);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->partnerInforServices->getListPartnerInfor($request);

        if (!$result["success"]) {
            return $this->errorResponse($result["message"], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get partner information by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->partnerInforValidation->detailPartnerInforValidation($id);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->partnerInforServices->getPartnerInforDetail($id);
        if (!$result["success"]) {
            $statusCode = $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse($result["data"], $result["message"], $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * update Partner information by ID
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->partnerInforValidation->updatePartnerInforValidation($request, $id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->partnerInforServices->updatePartnerInfor($request, $id);
        if (!$result["success"]) {
            $statusCode = $result["data"] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse($result["data"], $result["message"], $statusCode);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Get current authenticated partner information
     *
     * @return JsonResponse
     */
    public function showSelf(): JsonResponse
    {
        $result = $this->partnerInforServices->handleGetProfileForPartner();
        if (!$result["success"]) {
            return $this->errorResponse($result["data"], $result["message"], HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }

    /**
     * Update current authenticated partner information
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSelf(Request $request): JsonResponse
    {
        $result = $this->partnerInforServices->handleUpdateProfileForPartner($request);
        if (!$result["success"]) {
            return $this->errorResponse($result["data"], $result["message"], HttpStatus::INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse($result["data"], $result["message"]);
    }
}

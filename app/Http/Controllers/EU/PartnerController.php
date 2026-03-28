<?php

declare(strict_types=1);

namespace App\Http\Controllers\EU;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\PartnerInforServices;
use Illuminate\Http\JsonResponse;
use Mpdf\Tag\P;

final class PartnerController extends Controller
{
    /**
     * Service layer that handles business logic for rooms.
     * Validation layer that handles request data validation for rooms.
     */
    protected PartnerInforServices $partnerInforServices;

    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependencies
     * using Dependency Injection.
     */
    public function __construct(PartnerInforServices $partnerInforServices)
    {
        $this->partnerInforServices = $partnerInforServices;
    }

    /**
     * Get partners by province ID
     * @param int $provinceId
     * @return JsonResponse
     */
    public function getPartnersByProvinceId(int $provinceId): JsonResponse
    {
        $result = $this->partnerInforServices->handleGetPartnersByProvinceId($provinceId);

        if (!$result["success"]) {
            return $this->errorResponse(
                $result["message"],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result["data"],
            $result["message"]
        );
    }

    /**
     * Get partner detail by ID
     * @param int $id
     * @return JsonResponse
     */
    public function partnerDetail(int $id): JsonResponse
    {
        $result = $this->partnerInforServices->handlePartnerDetail($id);

        if (!$result["success"]) {
            return $this->errorResponse(
                $result["message"],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result["data"],
            $result["message"]
        );
    }
}

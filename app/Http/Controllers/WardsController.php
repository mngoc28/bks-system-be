<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\WardValidation;
use App\Services\WardsServices;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;

final class WardsController extends Controller
{
    /**
     * Wards services and validation instance
     */
    protected WardsServices $wardsServices;
    /**
     * Ward validation instance
     */
    protected WardValidation $wardsValidation;

    /**
     * Constructor
     * @param WardsServices $wardsServices
     * @param WardValidation $wardsValidation
     */
    public function __construct(WardsServices $wardsServices, WardValidation $wardsValidation)
    {
        $this->wardsServices = $wardsServices;
        $this->wardsValidation = $wardsValidation;
    }

    /**
     * Get wards by province id
     * @param int $provinceId
     * @return JsonResponse
     */
    public function getWardsByProvinceId(int $provinceId): JsonResponse
    {
        $validator = $this->wardsValidation->getWardsByProvinceIdValidation($provinceId);
        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->wardsServices->getWardsByProvinceId($provinceId);
        if ($result === null) {
            return $this->errorResponse(
                __('ward.messages.get_wards_by_province_id_failed'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }
        return $this->successResponse(
            $result,
            __('ward.messages.get_wards_by_province_id_success')
        );
    }
}

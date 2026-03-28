<?php

namespace App\Http\Controllers;

use App\Services\PricePackageService;
use App\Enums\HttpStatus;
use Illuminate\Http\JsonResponse;

class PricePackageController extends Controller
{
    protected $pricePackageService;

    /**
     * Summary of __construct
     * @param PricePackageService $pricePackageService
     */
    public function __construct(PricePackageService $pricePackageService)
    {
        $this->pricePackageService = $pricePackageService;
    }

    /**
     * Get all price packages
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result = $this->pricePackageService->getAllPricePackages();
        if (! $result['success']) {
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
     * Get price packages by room ID
     * @param int $roomId
     * @return JsonResponse
     */
    public function getByRoomId($roomId): JsonResponse
    {
        $result = $this->pricePackageService->getPricePackagesByRoomId((int) $roomId);
        if (! $result['success']) {
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

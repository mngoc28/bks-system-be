<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\DashboardValidation;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    protected DashboardService $dashboardService;
    protected DashboardValidation $dashboardValidation;

    /**
     * Constructor
     *
     * @param DashboardService $dashboardService
     * @param DashboardValidation $dashboardValidation
     */
    public function __construct(DashboardService $dashboardService, DashboardValidation $dashboardValidation)
    {
        $this->dashboardService    = $dashboardService;
        $this->dashboardValidation = $dashboardValidation;
    }

    /**
     * Get information about the number of users
     * @param Request $request
     * @return JsonResponse
     */
    public function getTotalUsers(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getTotalUsers($request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message'], HttpStatus::OK);
        }

        return $this->errorResponse($result['message']);
    }

    /**
     * Get information about the number of partner
     * @param Request $request
     * @return JsonResponse
     */
    public function getTotalPartner(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);
        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getTotalPartner($request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message'], HttpStatus::OK);
        }

        return $this->errorResponse($result['message']);
    }

    /**
     * get total of buildings in the system
     * @return JsonResponse
     */
    public function getSystemBuilding(): JsonResponse
    {
        $result = $this->dashboardService->getSystemBuilding();
        if ($result['success']) {
            return $this->successResponse(
                $result['data'],
                $result['message'],
                HttpStatus::OK
            );
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * get rooms in the system
     * @return JsonResponse
     */
    public function getSystemRoom(): JsonResponse
    {
        $result = $this->dashboardService->getSystemRoom();

        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], HttpStatus::BAD_REQUEST, null);
    }

    /**
     * Get bookings per month
     * @param Request $request
     * @return JsonResponse
     */
    public function bookingsPerMonth(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getBookingsPerMonth($request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get revenue per month
     * @param Request $request
     * @return JsonResponse
     */
    public function revenuePerMonth(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getRevenuePerMonth($request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get bookings count for all buildings
     * @return JsonResponse
     */
    public function getAllBuildingsBookingsCount(): JsonResponse
    {
        $result = $this->dashboardService->getAllBuildingsBookingsCount();
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }
}

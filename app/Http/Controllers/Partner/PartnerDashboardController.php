<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\DashboardValidation;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PartnerDashboardController extends Controller
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
     * get total of buildings for partner
     * @return JsonResponse
     */
    public function getSystemBuilding(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getSystemBuildingForPartner($partnerId);
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
     * get rooms for partner
     * @return JsonResponse
     */
    public function getSystemRoom(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getSystemRoomForPartner($partnerId);

        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get bookings per month for partner
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

        $partnerId = Auth::id();
        $result = $this->dashboardService->getBookingsPerMonthForPartner($partnerId, $request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get revenue per month for partner
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

        $partnerId = Auth::id();
        $result = $this->dashboardService->getRevenuePerMonthForPartner($partnerId, $request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get bookings count for all buildings of partner
     * @return JsonResponse
     */
    public function getAllBuildingsBookingsCount(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getAllBuildingsBookingsCountForPartner($partnerId);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get summary stats for partner
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getStatsForPartner($partnerId);

        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get pending bookings for partner
     * @return JsonResponse
     */
    public function getPendingBookings(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getPendingBookingsForPartner($partnerId);

        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get urgent maintenances for partner
     * @return JsonResponse
     */
    public function getUrgentMaintenances(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getUrgentMaintenancesForPartner($partnerId);

        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get revenue analytics for partner
     * @return JsonResponse
     */
    public function getRevenueAnalytics(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getRevenueAnalyticsForPartner($partnerId);

        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }
}

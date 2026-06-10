<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\DashboardValidation;
use App\Services\DashboardService;
use App\Services\PartnerDashboardScopeResolver;
use App\Services\PartnerKpiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PartnerDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected DashboardValidation $dashboardValidation,
        protected PartnerKpiService $partnerKpiService,
        protected PartnerDashboardScopeResolver $dashboardScopeResolver,
    ) {
    }

    /**
     * Get headline KPIs (occupancy, GMV, net revenue, time-to-confirm,
     * pending count, overbooking count) for the authenticated partner.
     *
     * Query: property_id (optional)
     */
    public function getKpis(Request $request): JsonResponse
    {
        $scope = $this->resolveScope($request);
        if ($scope['error'] !== null) {
            return $this->errorResponse($scope['error']['message'], null, $scope['error']['status']);
        }

        $partnerId = (int) Auth::id();
        $result = $this->partnerKpiService->getDashboardKpis($partnerId, $scope['propertyId']);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get 30-day occupancy chart data for the authenticated partner.
     *
     * Query: property_id (optional)
     */
    public function getOccupancyChart(Request $request): JsonResponse
    {
        $scope = $this->resolveScope($request);
        if ($scope['error'] !== null) {
            return $this->errorResponse($scope['error']['message'], null, $scope['error']['status']);
        }

        $partnerId = (int) Auth::id();
        $result = $this->partnerKpiService->getOccupancyChart($partnerId, $scope['propertyId']);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get 30-day GMV / net revenue chart data for the authenticated partner.
     *
     * Query: property_id (optional)
     */
    public function getGmvChart(Request $request): JsonResponse
    {
        $scope = $this->resolveScope($request);
        if ($scope['error'] !== null) {
            return $this->errorResponse($scope['error']['message'], null, $scope['error']['status']);
        }

        $partnerId = (int) Auth::id();
        $result = $this->partnerKpiService->getGmvChart($partnerId, $scope['propertyId']);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get total properties for partner.
     *
     * @return JsonResponse
     */
    public function getSystemProperty(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getSystemPropertyForPartner($partnerId);
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
     * Get bookings count grouped by property for partner.
     *
     * @return JsonResponse
     */
    public function getAllPropertiesBookingsCount(): JsonResponse
    {
        $partnerId = Auth::id();
        $result = $this->dashboardService->getAllPropertiesBookingsCountForPartner($partnerId);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get summary stats for partner.
     *
     * Query: property_id (optional)
     */
    public function getStats(Request $request): JsonResponse
    {
        $scope = $this->resolveScope($request);
        if ($scope['error'] !== null) {
            return $this->errorResponse($scope['error']['message'], null, $scope['error']['status']);
        }

        $partnerId = (int) Auth::id();
        $result = $this->dashboardService->getStatsForPartner($partnerId, $scope['propertyId']);

        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Get pending bookings for partner.
     *
     * Query: property_id (optional), limit (optional, max 20)
     */
    public function getPendingBookings(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDashboardScope($request);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $scope = $this->resolveScope($request);
        if ($scope['error'] !== null) {
            return $this->errorResponse($scope['error']['message'], null, $scope['error']['status']);
        }

        $partnerId = (int) Auth::id();
        $limit = (int) ($request->input('limit') ?? 10);
        $result = $this->dashboardService->getPendingBookingsForPartner(
            $partnerId,
            $limit,
            $scope['propertyId'],
        );

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

    /**
     * @return array{propertyId: int|null, error: array{message: string, status: HttpStatus}|null}
     */
    private function resolveScope(Request $request): array
    {
        $validator = $this->dashboardValidation->validateDashboardScope($request);
        if ($validator->fails()) {
            return [
                'propertyId' => null,
                'error'      => [
                    'message' => $validator->errors()->first(),
                    'status'  => HttpStatus::VALIDATION_ERROR,
                ],
            ];
        }

        $propertyId = $request->filled('property_id') ? (int) $request->input('property_id') : null;
        $resolved = $this->dashboardScopeResolver->resolvePropertyId((int) Auth::id(), $propertyId);

        return [
            'propertyId' => $resolved['propertyId'],
            'error'      => $resolved['error'],
        ];
    }
}

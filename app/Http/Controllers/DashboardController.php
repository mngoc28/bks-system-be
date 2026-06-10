<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\DashboardValidation;
use App\Services\DashboardService;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    protected DashboardService $dashboardService;
    protected DashboardValidation $dashboardValidation;
    protected ReportingService $reportingService;

    /**
     * Constructor
     *
     * @param DashboardService $dashboardService
     * @param DashboardValidation $dashboardValidation
     * @param ReportingService $reportingService
     */
    public function __construct(
        DashboardService $dashboardService,
        DashboardValidation $dashboardValidation,
        ReportingService $reportingService,
    ) {
        $this->dashboardService    = $dashboardService;
        $this->dashboardValidation = $dashboardValidation;
        $this->reportingService    = $reportingService;
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
     * Get total properties in the system.
     *
     * @return JsonResponse
     */
    public function getSystemProperty(): JsonResponse
    {
        $result = $this->dashboardService->getSystemProperty();
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
     * Daily booking volume trend for admin analytics.
     */
    public function getBookingsTrend(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getBookingsTrend($request);
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
     * Get bookings count grouped by property.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllPropertiesBookingsCount(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getAllPropertiesBookingsCount($request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }
        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * Booking status breakdown for admin analytics section.
     */
    public function getBookingStatusBreakdown(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getBookingStatusBreakdown($request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * System-wide occupancy trend for admin analytics section.
     */
    public function getOccupancyChart(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->dashboardService->getOccupancyChartForAdmin($request);
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message']);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }

    /**
     * ADR / RevPAR and period-over-period comparison for admin analytics.
     */
    public function getRevenuePerformance(Request $request): JsonResponse
    {
        $validator = $this->dashboardValidation->validateDateRange($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $startDate = $request->input(
            'start_date',
            now()->subDays(6)->format('Y-m-d')
        );
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        try {
            $data = $this->reportingService->getAdminPeriodComparison($startDate, $endDate);

            return $this->successResponse(
                [
                    ...$data,
                    'dateRange' => [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ],
                ],
                __('dashboard.messages.revenue_performance_fetched'),
                HttpStatus::OK,
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                __('dashboard.messages.revenue_performance_fetch_failed'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }
    }

    /**
     * Get operational KPI stats for admin dashboard (system-wide).
     */
    public function getStats(): JsonResponse
    {
        $result = $this->dashboardService->getStatsForAdmin();
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message'], HttpStatus::OK);
        }

        return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
    }
}

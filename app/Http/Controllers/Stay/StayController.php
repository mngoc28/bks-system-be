<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stay;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\StayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class StayController extends Controller
{
    /**
     * @var StayService
     */
    protected $stayService;

    /**
     * Constructor
     *
     * @param StayService $stayService
     */
    public function __construct(StayService $stayService)
    {
        $this->stayService = $stayService;
    }

    /**
     * Get Dashboard Stats and Active Booking
     *
     * @return JsonResponse
     */
    public function getDashboard(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        try {
            $data = $this->stayService->getDashboardData($userId);
            return $this->successResponse($data, 'Stay dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve dashboard data', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get Booking History
     *
     * @return JsonResponse
     */
    public function getBookings(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        $bookings = $this->stayService->getBookingHistory($userId);
        return $this->successResponse($bookings, 'Booking history retrieved successfully');
    }
}

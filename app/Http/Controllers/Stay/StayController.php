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
     * @var \App\Http\Validations\StayValidation
     */
    protected $stayValidation;
    protected $stayService;

    /**
     * Constructor
     *
     * @param StayService $stayService
     * @param \App\Http\Validations\StayValidation $stayValidation
     */
    public function __construct(StayService $stayService, \App\Http\Validations\StayValidation $stayValidation)
    {
        $this->stayService = $stayService;
        $this->stayValidation = $stayValidation;
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

        $perPage = request()->input('per_page', 10);
        $bookings = $this->stayService->getBookingHistory((int) $userId, (int) $perPage);
        $bookings->getCollection()->each->append('total_amount');

        return $this->successResponse($bookings, 'Booking history retrieved successfully');
    }

    /**
     * Get Booking Detail
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        try {
            $booking = $this->stayService->getBookingDetail($id, (int) $userId);
            if (!$booking) {
                return $this->errorResponse('Booking not found', null, HttpStatus::NOT_FOUND);
            }
            $booking->append('total_amount');

            return $this->successResponse($booking, 'Booking details retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve booking details', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Extend Booking
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function extend(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        $idValidator = $this->stayValidation->detailBookingValidation($id);
        if ($idValidator->fails()) {
            return $this->errorResponse(
                $idValidator->errors()->first(),
                $idValidator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $bodyValidator = $this->stayValidation->extendBookingValidation($request);
        if ($bodyValidator->fails()) {
            return $this->errorResponse(
                $bodyValidator->errors()->first(),
                $bodyValidator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->stayService->extendBooking($id, (int)$userId, $request->input('new_end_date'));

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }

    /**
     * Submit deposit receipt for a booking
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function submitReceipt(\Illuminate\Http\Request $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'receipt_path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), $validator->errors(), HttpStatus::VALIDATION_ERROR);
        }

        $result = $this->stayService->submitDepositReceipt($id, (int)$userId, $request->input('receipt_path'));

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Validations\BookingValidation;
use App\Services\BookingService;

final class PartnerBookingController extends Controller
{
    /**
     * Booking services instance
     */
    protected BookingService $bookingService;

    /**
     * Booking validation instance
     */
    protected BookingValidation $bookingValidation;

    /**
     * Constructor
     *
     * @param BookingService $bookingService
     * @param BookingValidation $bookingValidation
     */
    public function __construct(BookingService $bookingService, BookingValidation $bookingValidation)
    {
        $this->bookingService    = $bookingService;
        $this->bookingValidation = $bookingValidation;
    }

    /**
     * Get bookings for partner
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->bookingValidation->searchBookingValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->bookingService->handleGetAllBookingsForPartner($request);

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
     * Check-in a booking
     *
     * @param int $id
     * @return JsonResponse
     */
    public function checkIn(int $id): JsonResponse
    {
        $result = $this->bookingService->handleCheckIn($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }

    /**
     * Check-out a booking
     *
     * @param int $id
     * @return JsonResponse
     */
    public function checkOut(int $id): JsonResponse
    {
        $result = $this->bookingService->handleCheckOut($id);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }
}

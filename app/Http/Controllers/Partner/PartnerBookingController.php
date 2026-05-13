<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\BulkBookingActionRequest;
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

    /**
     * Confirm a pending booking belonging to the partner.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $request->merge(['id' => $id]);
        $result = $this->bookingService->handleConfirmBooking($request, $id);

        if (! $result['success']) {
            if (($result['code'] ?? null) === 'BOOKING_CONFLICT') {
                return $this->errorResponse(
                    $result['message'],
                    'BOOKING_CONFLICT',
                    HttpStatus::CONFLICT,
                    $result['data'],
                );
            }

            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Move a booking to a new date range/room (drag-drop FE Calendar).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function move(Request $request, int $id): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            [
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
                'room_id'    => 'nullable|integer|min:1|exists:rooms,id',
            ],
        );

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $request->merge(['id' => $id]);
        $result = $this->bookingService->handleMove($request, $id);

        if (! $result['success']) {
            if (($result['code'] ?? null) === 'BOOKING_CONFLICT') {
                return $this->errorResponse(
                    $result['message'],
                    'BOOKING_CONFLICT',
                    HttpStatus::CONFLICT,
                    $result['data'],
                );
            }

            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Confirm up to 20 bookings in one partner action.
     *
     * @param BulkBookingActionRequest $request
     * @return JsonResponse
     */
    public function bulkConfirm(BulkBookingActionRequest $request): JsonResponse
    {
        $result = $this->bookingService->handleBulkConfirm($request, $request->input('ids', []));

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Cancel up to 20 bookings with a shared reason.
     *
     * @param BulkBookingActionRequest $request
     * @return JsonResponse
     */
    public function bulkCancel(BulkBookingActionRequest $request): JsonResponse
    {
        $result = $this->bookingService->handleBulkCancel(
            $request,
            $request->input('ids', []),
            (string) $request->input('reason'),
        );

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Cancel a booking with a mandatory reason.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validator = $this->bookingValidation->partnerCancelBookingValidation($request, $id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $request->merge(['id' => $id]);
        $result = $this->bookingService->handleCancelBooking($request, $id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Mark a confirmed booking as no-show.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function noShow(Request $request, int $id): JsonResponse
    {
        $validator = $this->bookingValidation->partnerNoShowBookingValidation($id);
        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        $request->merge(['id' => $id]);
        $result = $this->bookingService->handleNoShow($request, $id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}

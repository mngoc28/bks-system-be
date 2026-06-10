<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Validations\BookingValidation;
use App\Services\BookingService;

final class BookingController extends Controller
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
     * Summary of index
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
        $result = $this->bookingService->handleGetAllOrSearchBookings($request);

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
     * Summary of show
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->bookingValidation->detailBookingValidation($id);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handleGetBookingById($id);

        if (! $result['success']) {
            $statusCode = $result['data'] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse(
                $result['message'],
                null,
                $statusCode
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Summary of store
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->bookingValidation->createBookingValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handleCreateBooking($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->createdResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Update booking (admin)
     * - Allows updating start_date, end_date, status
     * - Business rules reference confirm booking logic
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->bookingValidation->updateBookingValidation($request, $id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handleUpdateBooking($request, $id);
        if (! $result['success']) {
            $statusCode = $result['data'] === null
                ? HttpStatus::BAD_REQUEST
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse(
                $result['message'],
                null,
                $statusCode
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Delete booking (admin)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->bookingValidation->destroyBookingValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handleDestroyBooking($id);
        if (! $result['success']) {
            $statusCode = $result['data'] === null
                ? HttpStatus::BAD_REQUEST
                : HttpStatus::INTERNAL_SERVER_ERROR;
            return $this->errorResponse(
                $result['message'],
                null,
                $statusCode
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Summary of cancel
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $validator = $this->bookingValidation->cancelBookingValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->bookingService->handleCancelBooking($request, $id);

        if (! $result['success']) {
            $statusCode = $result['data'] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::BAD_REQUEST;
            return $this->errorResponse(
                $result['message'],
                null,
                $statusCode
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Confirm booking for partner/admin workflow
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function confirmBooking(Request $request, int $id): JsonResponse
    {
        $validator = $this->bookingValidation->confirmBookingValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handleConfirmBooking($request, $id);

        if (! $result['success']) {
            $statusCode = $result['data'] === null
                ? HttpStatus::NOT_FOUND
                : HttpStatus::BAD_REQUEST;
            return $this->errorResponse(
                $result['message'],
                null,
                $statusCode
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * ============================================
     * USER API
     * ============================================
     */

    /**
     * Summary of userCreateBooking
     * @param Request $request
     * @param int $roomId
     * @return JsonResponse
     */
    public function userCreateBooking(Request $request, int $roomId): JsonResponse
    {
        $validator = $this->bookingValidation->userCreateBookingValidation($request, $roomId);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handleUserCreateBooking($request, $roomId);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->createdResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Public lookup of a booking by email + booking code (rate limited).
     */
    public function publicLookupBooking(Request $request): JsonResponse
    {
        $validator = $this->bookingValidation->publicBookingLookupValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handlePublicBookingLookup($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::NOT_FOUND
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Public update guest email (before paid).
     */
    public function publicUpdateBookingEmail(Request $request): JsonResponse
    {
        $validator = $this->bookingValidation->publicUpdateBookingEmailValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->bookingService->handlePublicUpdateBookingEmail($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            null,
            $result['message']
        );
    }
}

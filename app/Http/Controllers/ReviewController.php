<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\ReviewValidation;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ReviewController extends Controller
{
    private ReviewService $reviewService;
    private ReviewValidation $reviewValidation;

    public function __construct(
        ReviewService $reviewService,
        ReviewValidation $reviewValidation
    ) {
        $this->reviewService = $reviewService;
        $this->reviewValidation = $reviewValidation;
    }

    /**
     * Submit reviews for a room and/or partner.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->reviewValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Dữ liệu đánh giá không hợp lệ',
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $userId = (int)Auth::id();
        $result = $this->reviewService->submitBookingReviews($userId, $request->all());

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'] ?? null,
            $result['message']
        );
    }

    /**
     * Get list of reviews for a room.
     *
     * @param int $roomId
     * @return JsonResponse
     */
    public function getRoomReviews(int $roomId): JsonResponse
    {
        $result = $this->reviewService->getRoomReviews($roomId);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get list of reviews for a partner.
     *
     * @param int $partnerId
     * @return JsonResponse
     */
    public function getPartnerReviews(int $partnerId): JsonResponse
    {
        $result = $this->reviewService->getPartnerReviews($partnerId);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get reviews for the public landing page.
     *
     * @return JsonResponse
     */
    public function getLandingPageReviews(): JsonResponse
    {
        $result = $this->reviewService->getLandingPageReviews();

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get reviews for a specific booking.
     *
     * @param int $bookingId
     * @return JsonResponse
     */
    public function getBookingReviews(int $bookingId): JsonResponse
    {
        $result = $this->reviewService->getBookingReviews($bookingId);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse($result['data'], $result['message']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stay;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\StayService;
use App\Http\Validations\StayValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class StayServiceController extends Controller
{
    /**
     * @var StayService
     */
    protected $stayService;

    /**
     * @var StayValidation
     */
    protected $stayValidation;

    /**
     * Constructor
     *
     * @param StayService $stayService
     * @param StayValidation $stayValidation
     */
    public function __construct(StayService $stayService, StayValidation $stayValidation)
    {
        $this->stayService = $stayService;
        $this->stayValidation = $stayValidation;
    }

    /**
     * Get services available for a booking
     *
     * @param int $bookingId
     * @return JsonResponse
     */
    public function index(int $bookingId): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        // Use validation for booking ID
        $validator = $this->stayValidation->detailBookingValidation($bookingId);
        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors()->first(),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        // We can reuse getDashboardData or specialized service logic
        // For now, return all services (as in original controller)
        // But in a more real scenario, this would be filtered by booking/room
        $services = \App\Models\Service::all();

        return $this->successResponse($services, 'Services retrieved successfully');
    }

    /**
     * Order a service for a booking
     *
     * @param Request $request
     * @param int $bookingId
     * @return JsonResponse
     */
    public function order(Request $request, int $bookingId): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        // Validate booking ID
        $idValidator = $this->stayValidation->detailBookingValidation($bookingId);
        if ($idValidator->fails()) {
            return $this->errorResponse(
                $idValidator->errors()->first(),
                $idValidator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        // Validate request body
        $bodyValidator = $this->stayValidation->serviceOrderValidation($request);
        if ($bodyValidator->fails()) {
            return $this->errorResponse(
                $bodyValidator->errors()->first(),
                $bodyValidator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $serviceId = (int)$request->input('service_id');
        $note = $request->input('note');
        $result = $this->stayService->orderService($userId, $bookingId, $serviceId, $note);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse(null, $result['message']);
    }
}

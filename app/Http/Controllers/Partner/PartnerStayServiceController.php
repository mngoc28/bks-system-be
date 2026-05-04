<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\StayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PartnerStayServiceController extends Controller
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
     * List all stay service requests for partner's buildings
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $partnerId = Auth::id();
        if (!$partnerId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        try {
            $requests = $this->stayService->getPartnerServiceRequests((int)$partnerId);
            return $this->successResponse($requests, 'Stay service requests retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve service requests', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update status of a service request
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $partnerId = Auth::id();
        if (!$partnerId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        $status = $request->input('status');
        if (!isset($status)) {
            return $this->errorResponse('Status is required', null, HttpStatus::VALIDATION_ERROR);
        }

        try {
            $result = $this->stayService->updateServiceRequestStatus($id, (int)$partnerId, (int)$status);
            if (!$result['success']) {
                return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
            }
            return $this->successResponse(null, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update request status', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }
}

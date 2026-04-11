<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stay;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\StayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class StayContractController extends Controller
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
     * Get user's contracts
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }
        
        $contracts = $this->stayService->getContracts($userId);
        return $this->successResponse($contracts, 'Contracts retrieved successfully');
    }

    /**
     * Get contract detail
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
        
        $contract = $this->stayService->getContractDetail($id, $userId);

        if (!$contract) {
            return $this->errorResponse('Contract not found', null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($contract, 'Contract details retrieved successfully');
    }
}

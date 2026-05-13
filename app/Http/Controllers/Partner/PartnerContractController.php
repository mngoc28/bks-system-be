<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\ContractService;
use App\Enums\HttpStatus;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerContractController extends Controller
{
    use ApiResponser;

    /**
     * @var ContractService
     */
    protected $contractService;

    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * GET /partner/contracts
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result = $this->contractService->handleGetPartnerContracts();

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
     * POST /partner/contracts
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
        ]);

        $result = $this->contractService->handleCreateContract($request);

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
     * GET /partner/contracts/{id}
     *
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->contractService->handleGetPartnerContractDetail($id);

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
     * GET /partner/contracts/expiring-soon
     *
     * Feeds the Phase 5 Alert Center tile "Contract sắp hết hạn".
     *
     * @return JsonResponse
     */
    public function expiringSoon(): JsonResponse
    {
        $result = $this->contractService->handleGetExpiringContractsForPartner();

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST,
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message'],
        );
    }

    /**
     * PUT /partner/contracts/{id}/renewal-reminder
     *
     * Body: { remind_at?: ISO8601 } — defaults to now() when omitted.
     *
     * @return JsonResponse
     */
    public function setRenewalReminder(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'remind_at' => 'nullable|date',
        ]);

        $remindAt = $request->filled('remind_at')
            ? Carbon::parse((string) $request->input('remind_at'))
            : Carbon::now();

        $result = $this->contractService->setRenewalReminder($id, $remindAt);

        if (! $result['success']) {
            $statusMap = [
                'CONTRACT_NOT_FOUND'   => HttpStatus::NOT_FOUND,
                'CONTRACT_FORBIDDEN'   => HttpStatus::FORBIDDEN,
                'CONTRACT_NOT_LEASE'   => HttpStatus::VALIDATION_ERROR,
                'CONTRACT_TERMINATED'  => HttpStatus::VALIDATION_ERROR,
            ];
            $status = $statusMap[$result['code'] ?? ''] ?? HttpStatus::BAD_REQUEST;

            return $this->errorResponse(
                $result['message'],
                $result['code'] ?? null,
                $status,
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message'],
        );
    }

    /**
     * POST /partner/contracts/{id}/terminate
     *
     * Body: { reason: string|min:5|max:500 }
     *
     * @return JsonResponse
     */
    public function terminate(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $result = $this->contractService->terminate($id, (string) $request->input('reason'));

        if (! $result['success']) {
            $statusMap = [
                'CONTRACT_NOT_FOUND'                  => HttpStatus::NOT_FOUND,
                'CONTRACT_FORBIDDEN'                  => HttpStatus::FORBIDDEN,
                'CONTRACT_ALREADY_TERMINATED'         => HttpStatus::VALIDATION_ERROR,
                'CONTRACT_TERMINATE_REASON_REQUIRED'  => HttpStatus::VALIDATION_ERROR,
            ];
            $status = $statusMap[$result['code'] ?? ''] ?? HttpStatus::BAD_REQUEST;

            return $this->errorResponse(
                $result['message'],
                $result['code'] ?? null,
                $status,
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message'],
        );
    }
}

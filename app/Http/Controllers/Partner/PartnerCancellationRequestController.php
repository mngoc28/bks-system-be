<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\PartnerCancellationApproveRequest;
use App\Http\Requests\Partner\PartnerCancellationRejectRequest;
use App\Services\PartnerCancellationRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class PartnerCancellationRequestController extends Controller
{
    public function __construct(
        private readonly PartnerCancellationRequestService $partnerCancellationRequestService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $partnerId = (int) Auth::id();
        if ($partnerId < 1) {
            return $this->errorResponse(__('booking.messages.unauthorized'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $validated = $request->validate([
            'status'      => [
                'sometimes', 'nullable', 'string',
                Rule::in(['pending', 'approved', 'rejected', 'withdrawn'])
            ],
            'property_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'per_page'    => ['sometimes', 'nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $filters = [
            'status'      => $validated['status'] ?? null,
            'property_id' => isset($validated['property_id']) ? (int) $validated['property_id'] : null,
            'per_page'    => isset($validated['per_page']) ? (int) $validated['per_page'] : null,
        ];

        $payload = $this->partnerCancellationRequestService->listForPartner($partnerId, $filters);

        return $this->successResponse($payload, __('booking.bcp.partner_inbox_loaded'));
    }

    public function approve(PartnerCancellationApproveRequest $request, int $id): JsonResponse
    {
        $partnerId = (int) Auth::id();
        if ($partnerId < 1) {
            return $this->errorResponse(__('booking.messages.unauthorized'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $row = $this->partnerCancellationRequestService->findForPartner($partnerId, $id);
        if ($row === null) {
            return $this->errorResponse(__('booking.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $this->authorize('view', $row);

        $note = $request->input('note');
        $result = $this->partnerCancellationRequestService->approve(
            $partnerId,
            $id,
            $note !== null ? (string) $note : null,
        );

        return $this->mapServiceResult($result);
    }

    public function reject(PartnerCancellationRejectRequest $request, int $id): JsonResponse
    {
        $partnerId = (int) Auth::id();
        if ($partnerId < 1) {
            return $this->errorResponse(__('booking.messages.unauthorized'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $row = $this->partnerCancellationRequestService->findForPartner($partnerId, $id);
        if ($row === null) {
            return $this->errorResponse(__('booking.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $this->authorize('view', $row);

        $result = $this->partnerCancellationRequestService->reject(
            $partnerId,
            $id,
            (string) $request->input('note'),
        );

        return $this->mapServiceResult($result);
    }

    /**
     * @param array{success: bool, data: mixed, message: string, code?: string, http_status?: int} $result
     */
    private function mapServiceResult(array $result): JsonResponse
    {
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message'], HttpStatus::OK);
        }

        $http       = (int) ($result['http_status'] ?? 400);
        $statusEnum = $this->httpStatusFromInt($http);
        $errPayload = isset($result['code']) ? ['code' => $result['code']] : null;

        return $this->errorResponse(
            $result['message'],
            $result['code'] ?? null,
            $statusEnum,
            $errPayload,
        );
    }

    private function httpStatusFromInt(int $http): HttpStatus
    {
        return match ($http) {
            403 => HttpStatus::FORBIDDEN,
            404 => HttpStatus::NOT_FOUND,
            409 => HttpStatus::CONFLICT,
            422 => HttpStatus::VALIDATION_ERROR,
            429 => HttpStatus::TOO_MANY_REQUESTS,
            500 => HttpStatus::INTERNAL_SERVER_ERROR,
            default => HttpStatus::BAD_REQUEST,
        };
    }
}

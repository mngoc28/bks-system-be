<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stay;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Stay\StayCancelBookingRequest;
use App\Http\Requests\Stay\StayCancelRequestBookingRequest;
use App\Models\Booking;
use App\Models\CancellationReasonCode;
use App\Services\GuestCancellationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class StayBookingCancellationController extends Controller
{
    public function __construct(
        private readonly GuestCancellationService $guestCancellationService,
    ) {
    }

    public function cancellationReasons(): JsonResponse
    {
        $rows = CancellationReasonCode::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'label_vi', 'requires_note']);

        $data = $rows->map(static function ($r): array {
            return [
                'code'          => (string) $r->code,
                'label'         => (string) $r->label_vi,
                'requires_note' => (bool) $r->requires_note,
            ];
        })->values()->all();

        return $this->successResponse($data, __('booking.bcp.reasons_loaded'))
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function cancel(StayCancelBookingRequest $request, int $id): JsonResponse
    {
        $userId = (int) Auth::id();
        if ($userId < 1) {
            return $this->errorResponse(__('booking.messages.unauthorized'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $booking = Booking::query()->find($id);
        if ($booking === null) {
            return $this->errorResponse(__('booking.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $this->authorize('guestCancel', $booking);

        $result = $this->guestCancellationService->cancelDirect(
            $userId,
            $id,
            (string) $request->input('reason_code'),
            $request->input('reason_text') !== null ? (string) $request->input('reason_text') : null,
        );

        return $this->mapServiceResult($result);
    }

    public function cancelRequest(StayCancelRequestBookingRequest $request, int $id): JsonResponse
    {
        $userId = (int) Auth::id();
        if ($userId < 1) {
            return $this->errorResponse(__('booking.messages.unauthorized'), 'UNAUTHORIZED', HttpStatus::UNAUTHORIZED);
        }

        $booking = Booking::query()->find($id);
        if ($booking === null) {
            return $this->errorResponse(__('booking.messages.not_found'), 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $this->authorize('guestCancelRequest', $booking);

        $result = $this->guestCancellationService->requestCancellation(
            $userId,
            $id,
            (string) $request->input('reason_code'),
            $request->input('reason_text') !== null ? (string) $request->input('reason_text') : null,
            (string) $request->input('idempotency_key'),
        );

        return $this->mapServiceResult($result);
    }

    /**
     * @param array{
     *     success: bool,
     *     data: mixed,
     *     message: string,
     *     code?: string,
     *     http_status?: int,
     *     retry_after_seconds?: int
     * } $result
     */
    private function mapServiceResult(array $result): JsonResponse
    {
        if ($result['success']) {
            return $this->successResponse($result['data'], $result['message'], HttpStatus::OK);
        }

        $http = (int) ($result['http_status'] ?? 400);
        $statusEnum = $this->httpStatusFromInt($http);

        $payload = null;
        if (isset($result['retry_after_seconds'])) {
            $payload = [
                'code'                   => $result['code'] ?? 'CANCEL_REQUEST_COOLDOWN',
                'retry_after_seconds'    => $result['retry_after_seconds'],
            ];
        } elseif (isset($result['code'])) {
            $payload = ['code' => $result['code']];
        }

        return $this->errorResponse(
            $result['message'],
            $result['code'] ?? null,
            $statusEnum,
            $payload,
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

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Events\BookingCancelled;
use App\Events\CancellationRequestUpdated;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GuestCancellationService
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly BookingTimelineService $timelineService,
        private readonly RoomsRepositoryInterface $roomsRepository,
        private readonly CancellationPolicyResolver $cancellationPolicyResolver,
    ) {
    }

    /**
     * Seconds remaining until another cancel-request is allowed (null = ok now).
     */
    public static function cancelRequestCooldownRemainingSeconds(?Carbon $lastRequestedAt, int $cooldownSeconds): ?int
    {
        if ($lastRequestedAt === null || $cooldownSeconds <= 0) {
            return null;
        }

        $allowedAt = $lastRequestedAt->copy()->addSeconds($cooldownSeconds);
        $remaining = $allowedAt->getTimestamp() - Carbon::now()->getTimestamp();
        if ($remaining <= 0) {
            return null;
        }

        return $remaining;
    }

    /**
     * Stay guest: cancel while booking is still pending partner confirmation.
     *
     * @return array{
     *     success: bool,
     *     data: mixed,
     *     message: string,
     *     code?: string,
     *     http_status?: int,
     *     retry_after_seconds?: int
     * }
     */
    public function cancelDirect(int $userId, int $bookingId, string $reasonCode, ?string $reasonText): array
    {
        try {
            return DB::transaction(function () use ($userId, $bookingId, $reasonCode, $reasonText): array {
                /** @var Booking|null $booking */
                $booking = Booking::query()
                    ->whereKey($bookingId)
                    ->lockForUpdate()
                    ->first();

                if ($booking === null) {
                    return $this->fail(__('booking.messages.not_found'), 'NOT_FOUND', 404);
                }

                if ((int) $booking->user_id !== $userId) {
                    return $this->fail(__('booking.messages.unauthorized'), 'FORBIDDEN', 403);
                }

                if ($this->stayBlocked($booking)) {
                    return $this->fail(__('booking.bcp.stay_in_progress_no_cancel'), 'STAY_NOT_CANCELLABLE', 409);
                }

                if ((int) $booking->status !== BookingStatus::PENDING->value) {
                    return $this->fail(__('booking.bcp.direct_cancel_invalid_status'), 'INVALID_STATE', 409);
                }

                $fromStatus = $this->mapStatusName((int) $booking->status);
                $reasonDisplay = trim((string) ($reasonText ?? '')) !== ''
                    ? sprintf('[%s] %s', $reasonCode, trim((string) $reasonText))
                    : sprintf('[%s]', $reasonCode);

                $now = Carbon::now();
                $this->bookingRepository->update($bookingId, [
                    'status'              => BookingStatus::CANCELLED->value,
                    'cancelled_at'        => $now,
                    'cancellation_reason' => $reasonDisplay,
                ]);

                $fresh = $this->bookingRepository->find($bookingId);
                $this->timelineService->recordCancelled(
                    $bookingId,
                    $reasonDisplay,
                    $fromStatus,
                    $userId,
                    [
                        'reason_code'   => $reasonCode,
                        'cancelled_via' => 'stay_guest_direct',
                    ],
                );

                $this->maybeDispatchBookingCancelled($fresh, $userId, $reasonDisplay);

                return [
                    'success' => true,
                    'data'    => $fresh,
                    'message' => __('booking.messages.cancelled_successfully'),
                ];
            });
        } catch (Throwable $e) {
            Log::error('GuestCancellationService::cancelDirect failed', [
                'booking_id' => $bookingId,
                'error'      => $e->getMessage(),
            ]);

            return $this->fail(__('booking.messages.update_failed'), 'INTERNAL', 500);
        }
    }

    /**
     * Stay guest: request cancellation for a confirmed booking (Partner must approve).
     *
     * @return array{
     *     success: bool,
     *     data: mixed,
     *     message: string,
     *     code?: string,
     *     http_status?: int,
     *     retry_after_seconds?: int
     * }
     */
    public function requestCancellation(
        int $userId,
        int $bookingId,
        string $reasonCode,
        ?string $reasonText,
        string $idempotencyKey,
    ): array {
        $cooldown = (int) config('bcp.cancel_request_cooldown_seconds', 3600);

        try {
            return DB::transaction(function () use (
                $userId,
                $bookingId,
                $reasonCode,
                $reasonText,
                $idempotencyKey,
                $cooldown
            ): array {
                /** @var Booking|null $booking */
                $booking = Booking::query()
                    ->whereKey($bookingId)
                    ->lockForUpdate()
                    ->first();

                if ($booking === null) {
                    return $this->fail(__('booking.messages.not_found'), 'NOT_FOUND', 404);
                }

                if ((int) $booking->user_id !== $userId) {
                    return $this->fail(__('booking.messages.unauthorized'), 'FORBIDDEN', 403);
                }

                if ($this->stayBlocked($booking)) {
                    return $this->fail(__('booking.bcp.stay_in_progress_no_cancel'), 'STAY_NOT_CANCELLABLE', 409);
                }

                if ((int) $booking->status === BookingStatus::PENDING_CANCELLATION->value) {
                    $sameKey = BookingCancellationRequest::query()
                        ->where('booking_id', $bookingId)
                        ->where('idempotency_key', $idempotencyKey)
                        ->where('status', 'pending')
                        ->first();
                    if ($sameKey !== null) {
                        return $this->successReplay((int) $sameKey->id, $bookingId);
                    }

                    return $this->fail(__('booking.bcp.cancel_request_already_pending'), 'ALREADY_PENDING', 409);
                }

                if ((int) $booking->status !== BookingStatus::CONFIRMED->value) {
                    return $this->fail(__('booking.bcp.cancel_request_invalid_status'), 'INVALID_STATE', 409);
                }

                $existingKey = BookingCancellationRequest::query()
                    ->where('booking_id', $bookingId)
                    ->where('idempotency_key', $idempotencyKey)
                    ->orderByDesc('id')
                    ->first();
                if ($existingKey !== null) {
                    return $this->fail(__('booking.bcp.idempotency_key_reuse'), 'IDEMPOTENCY_REUSE', 409);
                }

                $lastRequestedRaw = BookingCancellationRequest::query()
                    ->where('booking_id', $bookingId)
                    ->max('requested_at');
                $lastCarbon = $lastRequestedRaw !== null ? Carbon::parse((string) $lastRequestedRaw) : null;
                $remaining = self::cancelRequestCooldownRemainingSeconds($lastCarbon, $cooldown);
                if ($remaining !== null) {
                    return [
                        'success'             => false,
                        'data'                => null,
                        'message'             => __('booking.bcp.cancel_request_cooldown'),
                        'code'                => 'CANCEL_REQUEST_COOLDOWN',
                        'http_status'         => 429,
                        'retry_after_seconds' => $remaining,
                    ];
                }

                $pendingExists = BookingCancellationRequest::query()
                    ->where('booking_id', $bookingId)
                    ->where('status', 'pending')
                    ->exists();
                if ($pendingExists) {
                    return $this->fail(__('booking.bcp.cancel_request_already_pending'), 'ALREADY_PENDING', 409);
                }

                $now = Carbon::now();
                $resolution = $this->cancellationPolicyResolver->resolveForBooking($booking, $now);
                $policyVersion = mb_substr($resolution->policyVersion, 0, 32);

                $requestRow = BookingCancellationRequest::query()->create([
                    'booking_id'              => $bookingId,
                    'requester_user_id'       => $userId,
                    'reason_code'             => $reasonCode,
                    'reason_text'             => $reasonText,
                    'status'                  => 'pending',
                    'idempotency_key'         => $idempotencyKey,
                    'previous_booking_status' => (int) $booking->status,
                    'policy_version_snapshot' => $policyVersion,
                    'requested_at'            => $now,
                    'resolved_at'             => null,
                    'resolved_by_user_id'     => null,
                    'partner_decision_note'   => null,
                ]);

                $this->bookingRepository->update($bookingId, [
                    'status'                      => BookingStatus::PENDING_CANCELLATION->value,
                    'pending_cancellation_since'  => $now,
                    'cancellation_policy_version' => $policyVersion,
                ]);

                $this->timelineService->recordGuestCancelRequested(
                    $bookingId,
                    BookingTimelineService::STATUS_CONFIRMED,
                    $userId,
                    array_merge(
                        [
                            'request_id'  => (int) $requestRow->id,
                            'reason_code' => $reasonCode,
                        ],
                        $resolution->toTimelineMetadataFragment(),
                    ),
                );

                $fresh = $this->bookingRepository->find($bookingId);

                if ($fresh instanceof Booking) {
                    $scope = $this->resolveBroadcastScope($fresh);
                    DB::afterCommit(function () use ($requestRow, $fresh, $scope): void {
                        if ($scope['partner_id'] === null || $scope['property_id'] === null) {
                            return;
                        }
                        try {
                            event(new CancellationRequestUpdated(
                                (int) $requestRow->id,
                                (int) $fresh->id,
                                (int) $scope['property_id'],
                                (int) $scope['partner_id'],
                                'pending',
                            ));
                        } catch (Throwable $e) {
                            Log::warning('CancellationRequestUpdated dispatch failed (guest cancel-request)', [
                                'request_id' => $requestRow->id,
                                'error'      => $e->getMessage(),
                            ]);
                        }
                    });
                }

                return [
                    'success' => true,
                    'data'    => [
                        'booking_id'     => $bookingId,
                        'booking_status' => BookingStatus::PENDING_CANCELLATION->value,
                        'request_id'     => (int) $requestRow->id,
                        'booking'        => $fresh,
                    ],
                    'message' => __('booking.bcp.cancel_request_submitted'),
                ];
            });
        } catch (Throwable $e) {
            Log::error('GuestCancellationService::requestCancellation failed', [
                'booking_id' => $bookingId,
                'error'      => $e->getMessage(),
            ]);

            return $this->fail(__('booking.messages.update_failed'), 'INTERNAL', 500);
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    private function successReplay(int $requestId, int $bookingId): array
    {
        $fresh = $this->bookingRepository->find($bookingId);

        return [
            'success' => true,
            'data'    => [
                'booking_id'     => $bookingId,
                'booking_status' => BookingStatus::PENDING_CANCELLATION->value,
                'request_id'     => $requestId,
                'booking'        => $fresh,
            ],
            'message' => __('booking.bcp.cancel_request_submitted'),
            'code'    => 'IDEMPOTENT_REPLAY',
        ];
    }

    /**
     * @param mixed $data
     *
     * @return array{success: bool, data: mixed, message: string, code?: string, http_status?: int}
     */
    private function fail(string $message, string $code, int $http, $data = null): array
    {
        return [
            'success'     => false,
            'data'        => $data,
            'message'     => $message,
            'code'        => $code,
            'http_status' => $http,
        ];
    }

    private function mapStatusName(int $status): string
    {
        return match ($status) {
            BookingStatus::PENDING->value                => BookingTimelineService::STATUS_PENDING,
            BookingStatus::CONFIRMED->value              => BookingTimelineService::STATUS_CONFIRMED,
            BookingStatus::CANCELLED->value              => BookingTimelineService::STATUS_CANCELLED,
            BookingStatus::COMPLETED->value              => BookingTimelineService::STATUS_COMPLETED,
            BookingStatus::PENDING_CANCELLATION->value   => BookingTimelineService::STATUS_PENDING_CANCELLATION,
            default                                      => 'unknown',
        };
    }

    private function maybeDispatchBookingCancelled(?Booking $booking, int $actorUserId, string $reason): void
    {
        if ($booking === null) {
            return;
        }

        $scope = $this->resolveBroadcastScope($booking);
        if ($scope['partner_id'] === null || $scope['property_id'] === null) {
            return;
        }

        try {
            BookingCancelled::dispatch(
                $booking,
                $scope['partner_id'],
                $scope['property_id'],
                $actorUserId,
                $reason,
            );
        } catch (Throwable $e) {
            Log::warning('Broadcast dispatch failed', [
                'event' => 'booking.cancelled',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array{partner_id: int|null, property_id: int|null}
     */
    private function resolveBroadcastScope(Booking $booking): array
    {
        $room = $this->roomsRepository->find((int) $booking->room_id);
        if ($room === null) {
            return ['partner_id' => null, 'property_id' => null];
        }

        $property = $room->property;
        if ($property === null) {
            return ['partner_id' => null, 'property_id' => null];
        }

        return [
            'partner_id'  => (int) $property->user_id,
            'property_id' => (int) $property->id,
        ];
    }

    /**
     * @return list<string>
     */
    private function blockedStayStatuses(): array
    {
        return ['checked_in', 'checked_out', 'no_show'];
    }

    private function stayBlocked(Booking $booking): bool
    {
        $st = (string) ($booking->stay_status ?? '');

        return in_array($st, $this->blockedStayStatuses(), true);
    }
}

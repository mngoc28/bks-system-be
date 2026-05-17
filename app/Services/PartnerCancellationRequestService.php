<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Events\BookingCancelled;
use App\Events\CancellationRequestUpdated;
use App\Models\Booking;
use App\Models\BookingCancellationRequest;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\PartnerCancellationRequestRepository\PartnerCancellationRequestRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PartnerCancellationRequestService
{
    public function __construct(
        private readonly PartnerCancellationRequestRepositoryInterface $cancellationRequestRepository,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly BookingTimelineService $timelineService,
        private readonly RoomsRepositoryInterface $roomsRepository,
        private readonly PartnerCalendarService $calendarService,
    ) {
    }

    /**
     * @param array{status?: string|null, property_id?: int|null, per_page?: int|null} $filters
     *
     * @return array{items: list<array<string, mixed>>, meta: array<string, int>}
     */
    public function listForPartner(int $partnerUserId, array $filters): array
    {
        $paginator = $this->cancellationRequestRepository->paginateForPartner($partnerUserId, $filters);

        $items = [];
        foreach ($paginator->items() as $row) {
            if ($row instanceof BookingCancellationRequest) {
                $items[] = $this->mapListItem($row);
            }
        }

        return [
            'items' => $items,
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }

    public function findForPartner(int $partnerUserId, int $requestId): ?BookingCancellationRequest
    {
        return $this->cancellationRequestRepository->findForPartner($partnerUserId, $requestId);
    }

    /**
     * @return array{success: bool, data: mixed, message: string, code?: string, http_status?: int}
     */
    public function approve(int $partnerUserId, int $requestId, ?string $partnerNote): array
    {
        try {
            return DB::transaction(function () use ($partnerUserId, $requestId, $partnerNote): array {
                $booking = Booking::query()
                    ->whereHas('room.property', static function ($q) use ($partnerUserId): void {
                        $q->where('user_id', $partnerUserId);
                    })
                    ->lockForUpdate()
                    ->whereHas('cancellationRequests', static function ($q) use ($requestId): void {
                        $q->whereKey($requestId);
                    })
                    ->first();

                if ($booking === null) {
                    return $this->fail(__('booking.messages.not_found'), 'NOT_FOUND', 404);
                }

                /** @var BookingCancellationRequest|null $req */
                $req = BookingCancellationRequest::query()
                    ->whereKey($requestId)
                    ->lockForUpdate()
                    ->first();

                if ($req === null || (int) $req->booking_id !== (int) $booking->id) {
                    return $this->fail(__('booking.messages.not_found'), 'NOT_FOUND', 404);
                }

                if ($req->status !== 'pending') {
                    return $this->fail(__('booking.bcp.partner_request_not_pending'), 'INVALID_STATE', 409);
                }

                if ((int) $booking->status !== BookingStatus::PENDING_CANCELLATION->value) {
                    return $this->fail(
                        __('booking.bcp.partner_booking_not_pending_cancellation'),
                        'INVALID_STATE',
                        409
                    );
                }

                $scope = $this->resolveBroadcastScope($booking);
                if ($scope['partner_id'] === null || $scope['property_id'] === null) {
                    return $this->fail(__('booking.messages.update_failed'), 'INTERNAL', 500);
                }

                if ((int) $scope['partner_id'] !== $partnerUserId) {
                    return $this->fail(__('booking.messages.unauthorized'), 'FORBIDDEN', 403);
                }

                $now               = Carbon::now();
                $trimmedPartnerNote = $partnerNote !== null ? trim($partnerNote) : '';
                $cancellationReason = sprintf(
                    '[guest_cancel_request_approved] request_id=%d reason_code=%s',
                    (int) $req->id,
                    (string) $req->reason_code,
                );
                if ($trimmedPartnerNote !== '') {
                    $cancellationReason .= ' partner_note=' . mb_substr($trimmedPartnerNote, 0, 500);
                }
                $guestReason = trim((string) ($req->reason_text ?? ''));
                if ($guestReason !== '') {
                    $cancellationReason .= ' guest_reason=' . mb_substr($guestReason, 0, 500);
                }

                $this->bookingRepository->update((int) $booking->id, [
                    'status'                      => BookingStatus::CANCELLED->value,
                    'cancelled_at'                => $now,
                    'cancellation_reason'         => $cancellationReason,
                    'pending_cancellation_since'  => null,
                    'cancellation_policy_version' => null,
                ]);

                $req->status                = 'approved';
                $req->resolved_at           = $now;
                $req->resolved_by_user_id   = $partnerUserId;
                $req->partner_decision_note = $trimmedPartnerNote !== '' ? $trimmedPartnerNote : null;
                $req->save();

                $this->timelineService->recordGuestCancelRequestApproved(
                    (int) $booking->id,
                    $partnerUserId,
                    [
                        'request_id'  => (int) $req->id,
                        'reason_code' => (string) $req->reason_code,
                    ],
                );

                $fresh = $this->bookingRepository->find((int) $booking->id);
                if (! $fresh instanceof Booking) {
                    return $this->fail(__('booking.messages.update_failed'), 'INTERNAL', 500);
                }

                DB::afterCommit(function () use ($fresh, $scope, $partnerUserId, $req, $cancellationReason): void {
                    $this->dispatchCancellationRequestUpdated(
                        (int) $req->id,
                        (int) $fresh->id,
                        (int) $scope['property_id'],
                        (int) $scope['partner_id'],
                        'approved',
                    );
                    $this->maybeDispatchBookingCancelled($fresh, $partnerUserId, $cancellationReason);
                });

                return [
                    'success' => true,
                    'data'    => [
                        'request' => $req->fresh(),
                        'booking' => $fresh,
                    ],
                    'message' => __('booking.bcp.partner_request_approved'),
                ];
            });
        } catch (Throwable $e) {
            Log::error('PartnerCancellationRequestService::approve failed', [
                'request_id' => $requestId,
                'error'      => $e->getMessage(),
            ]);

            return $this->fail(__('booking.messages.update_failed'), 'INTERNAL', 500);
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string, code?: string, http_status?: int}
     */
    public function reject(int $partnerUserId, int $requestId, string $partnerNote): array
    {
        try {
            return DB::transaction(function () use ($partnerUserId, $requestId, $partnerNote): array {
                $booking = Booking::query()
                    ->whereHas('room.property', static function ($q) use ($partnerUserId): void {
                        $q->where('user_id', $partnerUserId);
                    })
                    ->lockForUpdate()
                    ->whereHas('cancellationRequests', static function ($q) use ($requestId): void {
                        $q->whereKey($requestId);
                    })
                    ->first();

                if ($booking === null) {
                    return $this->fail(__('booking.messages.not_found'), 'NOT_FOUND', 404);
                }

                /** @var BookingCancellationRequest|null $req */
                $req = BookingCancellationRequest::query()
                    ->whereKey($requestId)
                    ->lockForUpdate()
                    ->first();

                if ($req === null || (int) $req->booking_id !== (int) $booking->id) {
                    return $this->fail(__('booking.messages.not_found'), 'NOT_FOUND', 404);
                }

                if ($req->status !== 'pending') {
                    return $this->fail(__('booking.bcp.partner_request_not_pending'), 'INVALID_STATE', 409);
                }

                if ((int) $booking->status !== BookingStatus::PENDING_CANCELLATION->value) {
                    return $this->fail(
                        __('booking.bcp.partner_booking_not_pending_cancellation'),
                        'INVALID_STATE',
                        409
                    );
                }

                $scope = $this->resolveBroadcastScope($booking);
                if ($scope['partner_id'] === null || $scope['property_id'] === null) {
                    return $this->fail(__('booking.messages.update_failed'), 'INTERNAL', 500);
                }

                if ((int) $scope['partner_id'] !== $partnerUserId) {
                    return $this->fail(__('booking.messages.unauthorized'), 'FORBIDDEN', 403);
                }

                $previousInt = (int) $req->previous_booking_status;
                $toTimeline   = $this->mapIntBookingStatusToTimeline($previousInt);

                $now = Carbon::now();

                $this->bookingRepository->update((int) $booking->id, [
                    'status'                      => $previousInt,
                    'pending_cancellation_since'  => null,
                    'cancellation_policy_version' => null,
                ]);

                $noteTrim = trim($partnerNote);
                $req->status                = 'rejected';
                $req->resolved_at           = $now;
                $req->resolved_by_user_id   = $partnerUserId;
                $req->partner_decision_note = $noteTrim;
                $req->save();

                $this->timelineService->recordGuestCancelRequestRejected(
                    (int) $booking->id,
                    $toTimeline,
                    mb_substr($noteTrim, 0, 2000),
                    $partnerUserId,
                    [
                        'request_id'          => (int) $req->id,
                        'restored_status_int' => $previousInt,
                    ],
                );

                $fresh = $this->bookingRepository->find((int) $booking->id);

                DB::afterCommit(function () use ($scope, $req, $fresh): void {
                    $this->forgetPartnerKpiCache((int) $scope['partner_id']);
                    try {
                        $this->calendarService->bumpVersion((int) $scope['partner_id']);
                    } catch (Throwable $e) {
                        Log::warning('PartnerCancellationRequestService: calendar bump failed', [
                            'partner_id' => $scope['partner_id'],
                            'error'      => $e->getMessage(),
                        ]);
                    }
                    if ($fresh instanceof Booking) {
                        $this->dispatchCancellationRequestUpdated(
                            (int) $req->id,
                            (int) $fresh->id,
                            (int) $scope['property_id'],
                            (int) $scope['partner_id'],
                            'rejected',
                        );
                    }
                });

                return [
                    'success' => true,
                    'data'    => [
                        'request' => $req->fresh(),
                        'booking' => $fresh,
                    ],
                    'message' => __('booking.bcp.partner_request_rejected'),
                ];
            });
        } catch (Throwable $e) {
            Log::error('PartnerCancellationRequestService::reject failed', [
                'request_id' => $requestId,
                'error'      => $e->getMessage(),
            ]);

            return $this->fail(__('booking.messages.update_failed'), 'INTERNAL', 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapListItem(BookingCancellationRequest $row): array
    {
        $property = $row->booking?->room?->property;
        $room     = $row->booking?->room;

        return [
            'id'            => (int) $row->id,
            'booking_id'    => (int) $row->booking_id,
            'status'        => (string) $row->status,
            'requested_at'  => $row->requested_at->toIso8601String(),
            'reason_code'   => (string) $row->reason_code,
            'reason_text'   => $row->reason_text,
            'booking_status'=> $row->booking !== null ? (int) $row->booking->status : null,
            'property'      => $property !== null ? [
                'id'   => (int) $property->id,
                'name' => (string) $property->name,
            ] : null,
            'room'          => $room !== null ? [
                'id'           => (int) $room->id,
                'title'        => (string) ($room->title ?? ''),
                'room_number'  => $room->room_number !== null ? (string) $room->room_number : null,
            ] : null,
        ];
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

    private function mapIntBookingStatusToTimeline(int $status): string
    {
        return match ($status) {
            BookingStatus::PENDING->value              => BookingTimelineService::STATUS_PENDING,
            BookingStatus::CONFIRMED->value            => BookingTimelineService::STATUS_CONFIRMED,
            BookingStatus::CANCELLED->value           => BookingTimelineService::STATUS_CANCELLED,
            BookingStatus::COMPLETED->value            => BookingTimelineService::STATUS_COMPLETED,
            BookingStatus::PENDING_CANCELLATION->value => BookingTimelineService::STATUS_PENDING_CANCELLATION,
            default                                    => 'unknown',
        };
    }

    private function forgetPartnerKpiCache(int $partnerId): void
    {
        try {
            foreach (PartnerKpiService::cacheKeysForPartner($partnerId) as $key) {
                Cache::forget($key);
            }
        } catch (Throwable $e) {
            Log::warning('PartnerCancellationRequestService: KPI cache forget failed', [
                'partner_id' => $partnerId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function dispatchCancellationRequestUpdated(
        int $requestId,
        int $bookingId,
        int $propertyId,
        int $partnerId,
        string $status,
    ): void {
        try {
            event(new CancellationRequestUpdated($requestId, $bookingId, $propertyId, $partnerId, $status));
        } catch (Throwable $e) {
            Log::warning('CancellationRequestUpdated dispatch failed', [
                'request_id' => $requestId,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function maybeDispatchBookingCancelled(Booking $booking, int $actorUserId, string $reason): void
    {
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
            Log::warning('BookingCancelled dispatch failed (partner approve cancel-request)', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string, code?: string, http_status?: int}
     */
    private function fail(string $message, string $code, int $http, mixed $data = null): array
    {
        return [
            'success'     => false,
            'data'        => $data,
            'message'     => $message,
            'code'        => $code,
            'http_status' => $http,
        ];
    }
}

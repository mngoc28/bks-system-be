<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Http\Resources\Partner\RoomMaintenanceResource;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomMaintenance;
use App\Models\User;
use App\Repositories\RoomMaintenanceRepository\RoomMaintenanceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RoomMaintenanceService
{
    /** @var array<string, list<string>> */
    private const ALLOWED_TRANSITIONS = [
        RoomMaintenance::STATUS_PLANNED => [
            RoomMaintenance::STATUS_IN_PROGRESS,
            RoomMaintenance::STATUS_CANCELLED,
        ],
        RoomMaintenance::STATUS_IN_PROGRESS => [
            RoomMaintenance::STATUS_COMPLETED,
            RoomMaintenance::STATUS_CANCELLED,
        ],
    ];

    public function __construct(
        private readonly RoomMaintenanceRepositoryInterface $roomMaintenanceRepository,
        private readonly MaintenanceBlockSyncService $blockSyncService,
        private readonly ConflictChecker $conflictChecker,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getList(array $filters): array
    {
        try {
            $partnerId = $this->resolvePartnerScope();
            if ($partnerId !== null) {
                $filters['partner_id'] = $partnerId;
            }

            $result = $this->roomMaintenanceRepository->getList($filters);

            return $this->formatPaginatedList($result);
        } catch (Throwable $exception) {
            Log::error('Failed to retrieve room maintenance list', [
                'filters'   => $filters,
                'exception' => $exception,
            ]);

            return [
                'current_page' => 1,
                'data'         => [],
                'last_page'    => 1,
                'per_page'     => 15,
                'total'        => 0,
            ];
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    public function getById(int $id): array
    {
        $maintenance = $this->roomMaintenanceRepository->findByIdForScope(
            $id,
            $this->resolvePartnerScope()
        );

        if ($maintenance === null) {
            return [
                'success' => false,
                'data'    => null,
                'message' => __('room_maintenance.not_found'),
                'code'    => 'MAINTENANCE_NOT_FOUND',
            ];
        }

        if (Gate::denies('view', $maintenance)) {
            return [
                'success' => false,
                'data'    => null,
                'message' => __('room_maintenance.unauthorized'),
                'code'    => 'MAINTENANCE_UNAUTHORIZED',
            ];
        }

        return [
            'success' => true,
            'data'    => (new RoomMaintenanceResource($maintenance))->resolve(),
            'message' => __('room_maintenance.detail_success'),
        ];
    }

    /**
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    public function previewCalendarConflicts(int $roomId, string $startDate, string $endDate): array
    {
        $room = Room::query()->with('property')->find($roomId);
        if ($room === null) {
            return [
                'success' => false,
                'data'    => null,
                'message' => __('room_maintenance.room_not_found'),
                'code'    => 'MAINTENANCE_NOT_FOUND',
            ];
        }

        if (Gate::denies('createForRoom', $room)) {
            return [
                'success' => false,
                'data'    => null,
                'message' => __('room_maintenance.not_found'),
                'code'    => 'MAINTENANCE_NOT_FOUND',
            ];
        }

        $conflicts = $this->conflictChecker->findConflicts($roomId, $startDate, $endDate);

        return [
            'success' => true,
            'data'    => [
                'has_conflict' => $conflicts['hasConflict'],
                'bookings'     => $conflicts['bookings']->map(fn (Booking $booking): array => [
                    'id'          => (int) $booking->id,
                    'start_date'  => optional($booking->start_date)->format('Y-m-d')
                        ?? (string) $booking->getRawOriginal('start_date'),
                    'end_date'    => optional($booking->end_date)->format('Y-m-d')
                        ?? (string) $booking->getRawOriginal('end_date'),
                    'status'      => (int) $booking->status,
                    'stay_status' => (string) ($booking->stay_status ?? ''),
                    'guest_name'  => (string) ($booking->user?->name ?? $booking->customer_name ?? ''),
                ])->values()->all(),
                'blocks' => $conflicts['blocks']->map(fn ($block): array => [
                    'id'         => (int) $block->id,
                    'block_type' => (string) $block->block_type,
                    'start_date' => optional($block->start_date)->format('Y-m-d')
                        ?? (string) $block->getRawOriginal('start_date'),
                    'end_date'   => optional($block->end_date)->format('Y-m-d')
                        ?? (string) $block->getRawOriginal('end_date'),
                    'reason'     => (string) ($block->reason ?? ''),
                ])->values()->all(),
                'current_stay' => $this->findCurrentStay($roomId),
            ],
            'message' => __('room_maintenance.conflict_preview_success'),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    public function create(array $payload): array
    {
        $blockError = null;

        try {
            $room = Room::query()->with('property')->find($payload['room_id']);
            if ($room === null) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_maintenance.create_failed'),
                    'code'    => 'MAINTENANCE_ROOM_NOT_FOUND',
                ];
            }

            if (Gate::denies('createForRoom', $room)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_maintenance.unauthorized'),
                    'code'    => 'MAINTENANCE_UNAUTHORIZED',
                ];
            }

            $payload['property_id'] = $payload['property_id'] ?? $room->property_id;
            $payload['status'] = RoomMaintenance::STATUS_PLANNED;
            $payload['created_by'] = Auth::id();
            $payload['source'] = RoomMaintenance::SOURCE_PARTNER;
            $payload['block_calendar'] = array_key_exists('block_calendar', $payload)
                ? (bool) $payload['block_calendar']
                : true;

            if ($payload['block_calendar'] && empty($payload['end_time']) && empty($payload['end_date'])) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_maintenance.end_time_required_for_block'),
                    'code'    => 'MAINTENANCE_VALIDATION_ERROR',
                ];
            }

            $maintenance = null;

            DB::transaction(function () use ($payload, $room, &$maintenance): void {
                $maintenance = $this->roomMaintenanceRepository->create($payload);

                $syncResult = $this->blockSyncService->attachBlockOnCreate($maintenance, $room);
                if (! $syncResult['success']) {
                    throw new \RuntimeException(json_encode($syncResult));
                }

                if (! empty($syncResult['room_block_id'])) {
                    $maintenance->room_block_id = (int) $syncResult['room_block_id'];
                    $maintenance->save();
                }
            });

            $maintenance?->load(['room', 'property']);

            return [
                'success' => true,
                'data'    => (new RoomMaintenanceResource($maintenance))->resolve(),
                'message' => __('room_maintenance.create_success'),
            ];
        } catch (\RuntimeException $exception) {
            $errorDetails = json_decode($exception->getMessage(), true);
            if (is_array($errorDetails)) {
                return [
                    'success' => false,
                    'data'    => $errorDetails['data'] ?? null,
                    'message' => (string) ($errorDetails['message'] ?? __('room_maintenance.create_failed')),
                    'code'    => (string) ($errorDetails['code'] ?? 'MAINTENANCE_BLOCK_FAILED'),
                ];
            }
        } catch (Throwable $exception) {
            Log::error('Failed to create room maintenance', [
                'payload'   => $payload,
                'exception' => $exception,
            ]);
        }


        return [
            'success' => false,
            'data'    => null,
            'message' => __('room_maintenance.create_failed'),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    public function update(int $id, array $payload): array
    {
        try {
            $maintenance = $this->roomMaintenanceRepository->findByIdForScope(
                $id,
                $this->resolvePartnerScope()
            );

            if ($maintenance === null) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_maintenance.not_found'),
                    'code'    => 'MAINTENANCE_NOT_FOUND',
                ];
            }

            if (Gate::denies('update', $maintenance)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_maintenance.unauthorized'),
                    'code'    => 'MAINTENANCE_UNAUTHORIZED',
                ];
            }

            if (! empty($payload['status'])) {
                $transitionResult = $this->applyStatusTransition($maintenance, (string) $payload['status'], $payload);
                if (! $transitionResult['success']) {
                    return $transitionResult;
                }
            }

            $updatable = array_filter([
                'description' => $payload['description'] ?? null,
                'end_time'    => $payload['end_time'] ?? null,
                'images'      => $payload['images'] ?? null,
            ], static fn ($value) => $value !== null);

            if ($updatable !== []) {
                $maintenance->fill($updatable);
                $maintenance->save();
            }

            $maintenance->load(['room', 'property']);

            return [
                'success' => true,
                'data'    => (new RoomMaintenanceResource($maintenance))->resolve(),
                'message' => __('room_maintenance.update_success'),
            ];
        } catch (Throwable $exception) {
            Log::error('Failed to update room maintenance', [
                'id'        => $id,
                'payload'   => $payload,
                'exception' => $exception,
            ]);
        }

        return [
            'success' => false,
            'data'    => null,
            'message' => __('room_maintenance.update_failed'),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    private function applyStatusTransition(
        RoomMaintenance $maintenance,
        string $newStatus,
        array $payload
    ): array {
        $currentStatus = (string) $maintenance->status;

        if ($currentStatus === $newStatus) {
            return ['success' => true, 'data' => null, 'message' => ''];
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
        if (! in_array($newStatus, $allowed, true)) {
            return [
                'success' => false,
                'data'    => null,
                'message' => __('room_maintenance.invalid_transition'),
                'code'    => 'MAINTENANCE_INVALID_TRANSITION',
            ];
        }

        if ($newStatus === RoomMaintenance::STATUS_CANCELLED) {
            $reason = trim((string) ($payload['cancellation_reason'] ?? ''));
            if ($reason === '') {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('room_maintenance.cancellation_reason_required'),
                    'code'    => 'MAINTENANCE_VALIDATION_ERROR',
                ];
            }
            $maintenance->cancellation_reason = $reason;
            $maintenance->cancelled_at = Carbon::now();
        }

        if ($newStatus === RoomMaintenance::STATUS_IN_PROGRESS) {
            $maintenance->started_at = Carbon::now();
        }

        if ($newStatus === RoomMaintenance::STATUS_COMPLETED) {
            $maintenance->completed_at = Carbon::now();
            if ($maintenance->end_time === null && ! empty($payload['end_time'])) {
                $maintenance->end_time = Carbon::parse($payload['end_time']);
            }
            if ($maintenance->end_time === null) {
                $maintenance->end_time = Carbon::now();
            }
        }

        $maintenance->status = $newStatus;
        $maintenance->save();

        if (in_array($newStatus, [RoomMaintenance::STATUS_COMPLETED, RoomMaintenance::STATUS_CANCELLED], true)) {
            $this->blockSyncService->releaseLinkedBlock($maintenance);
            if ($maintenance->room_block_id !== null) {
                $maintenance->room_block_id = null;
                $maintenance->save();
            }
        }

        return ['success' => true, 'data' => null, 'message' => ''];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPaginatedList(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'data'         => RoomMaintenanceResource::collection($paginator->items())->resolve(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }

    private function resolvePartnerScope(): ?int
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return null;
        }

        if ($user->role === 'partner') {
            return (int) $user->id;
        }

        return null;
    }

    /**
     * @return array{booking_id: int, guest_name: string, start_date: string, end_date: string, stay_status: string}|null
     */
    private function findCurrentStay(int $roomId): ?array
    {
        $today = Carbon::now()->toDateString();

        $booking = Booking::query()
            ->with('user')
            ->where('room_id', $roomId)
            ->whereNotIn('status', [
                BookingStatus::CANCELLED->value,
                BookingStatus::COMPLETED->value,
            ])
            ->where('stay_status', '!=', 'no_show')
            ->where(function ($query) use ($today): void {
                $query->where('stay_status', 'checked_in')
                    ->orWhere(function ($nested) use ($today): void {
                        $nested->where('start_date', '<=', $today)
                            ->where('end_date', '>', $today);
                    });
            })
            ->orderByDesc('id')
            ->first();

        if ($booking === null) {
            return null;
        }

        return [
            'booking_id'  => (int) $booking->id,
            'guest_name'  => (string) ($booking->user?->name ?? $booking->customer_name ?? ''),
            'start_date'  => optional($booking->start_date)->format('Y-m-d')
                ?? (string) $booking->getRawOriginal('start_date'),
            'end_date'    => optional($booking->end_date)->format('Y-m-d')
                ?? (string) $booking->getRawOriginal('end_date'),
            'stay_status' => (string) ($booking->stay_status ?? ''),
        ];
    }
}

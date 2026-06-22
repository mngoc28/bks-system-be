<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Repositories\RoomPriceRepository\RoomPriceRepositoryInterface;
use App\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Repositories\PricePackageRepository\PricePackageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Enums\UserType;
use App\Enums\Status as EnumsStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\BookingNoShow;
use App\Events\RoomInventoryReleased;
use App\Jobs\SendBooking;
use App\Jobs\SendBookingCancelled;
use App\Jobs\VerifyMail;
use App\Models\Booking;
use App\Models\Contract;
use App\Models\RoomPrice;
use App\Models\PartnerSettlementPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\BookingTimelineService;
use App\Services\ConflictChecker;
use App\Services\DepositService;
use Throwable;

final class BookingService
{
    /**
     * Booking repository instance
     */
    protected BookingRepositoryInterface $bookingRepository;
    protected RoomsRepositoryInterface $roomsRepository;
    protected RoomPriceRepositoryInterface $roomPriceRepository;
    protected UsersRepositoryInterface $usersRepository;
    protected PricePackageRepositoryInterface $pricePackageRepository;
    protected BookingTimelineService $timelineService;
    protected ConflictChecker $conflictChecker;
    protected DepositService $depositService;
    protected DynamicDepositPolicyService $policyService;
    protected ChatService $chatService;

    /**
     * Constructor
     *
     * @param BookingRepositoryInterface $bookingRepository
     */
    public function __construct(
        BookingRepositoryInterface $bookingRepository,
        RoomsRepositoryInterface $roomsRepository,
        RoomPriceRepositoryInterface $roomPriceRepository,
        UsersRepositoryInterface $usersRepository,
        PricePackageRepositoryInterface $pricePackageRepository,
        BookingTimelineService $timelineService,
        ConflictChecker $conflictChecker,
        DepositService $depositService,
        DynamicDepositPolicyService $policyService,
        ChatService $chatService,
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->roomsRepository = $roomsRepository;
        $this->roomPriceRepository = $roomPriceRepository;
        $this->usersRepository = $usersRepository;
        $this->pricePackageRepository = $pricePackageRepository;
        $this->timelineService = $timelineService;
        $this->conflictChecker = $conflictChecker;
        $this->depositService = $depositService;
        $this->policyService = $policyService;
        $this->chatService = $chatService;
    }

    /**
     * Get all bookings or search bookings
     *
     * @param Request $request
     * @return array{success: bool, data: LengthAwarePaginator|null, message: string}
     */
    public function handleGetAllOrSearchBookings($request): array
    {
        try {
            $bookings = $this->bookingRepository->getAllOrSearchBookings($request);

            return [
                'success' => true,
                'data'    => $bookings,
                'message' => __('booking.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('booking.messages.retrieved_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.retrieved_failed'),
            ];
        }
    }

    /**
     * Get booking by ID
     *
     * @param int $id
     * @return array{success: bool, data: Booking|null, message: string}
     */
    public function handleGetBookingById(int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);

            return [
                'success' => true,
                'data'    => $booking,
                'message' => __('booking.messages.found_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('booking.messages.find_failed'), [
                'booking_id' => $id,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.find_failed'),
            ];
        }
    }

    /**
     * Summary of handle createBooking
     * @param Request $request
     * @return array{success: bool, data: Booking|null, message:string}
     */
    public function handleCreateBooking($request): array
    {
        $data = [];

        try {
            $data = $request->all();

            // Authorization via repository helper (supports create with room_id)
            $authorized = $this->bookingRepository->checkUser($request);
            if (!$authorized) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized_staff_action'),
                ];
            }

            // Room in private cannot be booked
            if (Auth::user()->role == 'user' && $this->roomsRepository->find($data['room_id'])->status === false) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.room_in_private'),
                ];
            }

            // Check for room conflict
            $conflictBookings = $this->bookingRepository->checkRoomConflict(
                $data['room_id'],
                $data['start_date'],
                $data['end_date']
            );

            if ($conflictBookings) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.room_unavailable'),
                ];
            }

            // check room prices
            $priceExists = $this->bookingRepository->checkPriceExistsForRoom(
                $data['room_id'],
                $data['price_id']
            );
            if (!$priceExists) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __(
                        'booking.messages.not_exist_price',
                        ['price_id' => $data['price_id']]
                    ),
                ];
            }

            $data['created_by'] = Auth::id();
            // create new booking
            $data['status'] = $data['status'] ?? 0;
            $booking = $this->bookingRepository->create($data);

            // Create deposit if required by policy
            $this->depositService->createDeposit($booking);

            $this->bootstrapChatConversation($booking);

            // Realtime: broadcast booking mới đến partner sở hữu room (Phase 2).
            $scope = $this->resolveBroadcastScope($booking);
            if ($scope['partner_id'] !== null && $scope['property_id'] !== null) {
                $this->safeDispatch('booking.created', static function () use ($booking, $scope): void {
                    BookingCreated::dispatch($booking, $scope['partner_id'], $scope['property_id']);
                });
            }

            return [
                'success' => true,
                'data'    => $booking,
                'message' => __('booking.messages.created_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('booking.messages.create_failed'), [
                'data'  => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.create_failed'),
            ];
        }
    }

    /**
     * Cancel booking.
     *
     * For partner-initiated cancellations a non-empty `reason` field is
     * required; admin keeps backward compatibility (reason optional, falls
     * back to a system-generated note). Records `cancelled_at` and appends a
     * timeline event in the same transaction so the audit log is always
     * consistent with the booking row.
     *
     * @param Request $request
     * @param int $id
     * @return array{success: bool, data: mixed, message: string}
     */
    public function handleCancelBooking(Request $request, int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);

            if (!$booking) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.not_found'),
                ];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể hủy.',
                ];
            }

            $checkUser = $this->bookingRepository->checkUser($request);
            if (!$checkUser) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized'),
                ];
            }

            if ((int) $booking->status === BookingStatus::CANCELLED->value) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.already_cancelled'),
                ];
            }

            if (
                (int) $booking->status === BookingStatus::PENDING_CANCELLATION->value
                && in_array(Auth::user()->role ?? null, ['partner', 'admin'], true)
            ) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.partner_cancel_blocked_pending_cancellation'),
                    'code'    => 'PARTNER_CANCEL_BLOCKED_PENDING_CANCELLATION',
                ];
            }

            $role = Auth::user()->role ?? null;
            $reason = trim((string) $request->input('reason', ''));
            if ($role === 'partner' && $reason === '') {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.cancellation_reason_required'),
                ];
            }
            if ($reason === '') {
                $reason = sprintf('Cancelled by %s', $role ?? 'system');
            }

            $fromStatus = $this->mapStatusName((int) $booking->status);

            DB::beginTransaction();
            $now = Carbon::now();

            // Release the room (free the room status)
            $this->roomsRepository->update($booking->room_id, ['status' => true]);

            // Resolve cancellation policy to check if free cancel
            $resolver = new \App\Services\CancellationPolicyResolver();
            $resolution = $resolver->resolveForBooking($booking, $now);
            $isFreeCancel = (
                $resolution->refundPercent === 100.0
                || $resolution->feePercent === 0.0
                || $resolution->feePercent === null
            );

            if ($isFreeCancel) {
                // Free cancellation -> refund deposit
                $this->depositService->refundDeposit($id);
            } else {
                // Forfeit deposit (paid cancellation)
                $this->depositService->forfeitDeposit($id);
            }

            $this->bookingRepository->update($id, [
                'status'              => BookingStatus::CANCELLED->value,
                'cancelled_at'        => $now,
                'cancellation_reason' => $reason,
            ]);
            $bookingUpdate = $this->bookingRepository->find($id);

            // Broadcast Inventory Released event for Channel Manager (T3.3)
            event(new \App\Events\RoomInventoryReleased($booking->room_id));

            $this->timelineService->recordCancelled($id, $reason, $fromStatus, null, [
                'cancelled_at' => $now->toIso8601String(),
                'role'         => $role,
                'policy_resolution' => $resolution->toTimelineMetadataFragment(),
            ]);

            DB::commit();

            // --- Broadcast realtime event ---
            $scope = $this->resolveBroadcastScope($bookingUpdate);
            if ($scope['partner_id'] !== null && $scope['property_id'] !== null) {
                $actorId = Auth::id();
                $this->safeDispatch(
                    'booking.cancelled',
                    static function () use ($bookingUpdate, $scope, $actorId, $reason): void {
                        BookingCancelled::dispatch(
                            $bookingUpdate,
                            $scope['partner_id'],
                            $scope['property_id'],
                            $actorId,
                            $reason,
                        );
                    },
                );
            }

            // --- Send cancellation email to guest (after commit) ---
            // Eager-load user + room.property because BaseRepository::find() does not load relations.
            $bookingWithRelations = Booking::with(['user', 'room.property'])->find($id);
            $guest     = $bookingWithRelations?->user;
            $guestRoom = $bookingWithRelations?->room;
            if ($guest && $guest->email) {
                $startCarbon = Carbon::parse($bookingUpdate->start_date);
                $endCarbon   = Carbon::parse($bookingUpdate->end_date);
                $cancelledAt = Carbon::parse($bookingUpdate->cancelled_at)
                    ->timezone('Asia/Ho_Chi_Minh')
                    ->format('d/m/Y H:i:s');

                $cancelEmailData = [
                    'booking_code'        => (string) ($bookingUpdate->booking_code ?? ''),
                    'booking_created_at'  => Carbon::parse($bookingUpdate->created_at)
                        ->timezone('Asia/Ho_Chi_Minh')
                        ->format('d/m/Y H:i:s'),
                    'room_title'          => (string) ($guestRoom?->title ?? ''),
                    'property_name'       => (string) ($guestRoom?->property?->name ?? ''),
                    'property_address'    => (string) ($guestRoom?->property?->address_detail ?? ''),
                    'start_date'          => $startCarbon->format('d/m/Y'),
                    'end_date'            => $endCarbon->format('d/m/Y'),
                    'cancelled_at'        => $cancelledAt,
                    'cancellation_reason' => $reason,
                    'bookings_url'        => config('app.url_frontend') . '/bks-stay/bookings/' . $bookingUpdate->id,
                    'room_url'            => config('app.url_frontend') . '/rooms/' . $bookingUpdate->room_id,
                    'goline_phone'        => '0243 795 7250',
                ];

                SendBookingCancelled::dispatch($guest->email, $guest->name ?? $guest->email, $cancelEmailData);
            }

            return [
                'success' => true,
                'data'    => $bookingUpdate,
                'message' => __('booking.messages.cancelled_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(__('booking.messages.update_failed'), [
                'booking_id' => $id,
                'error'       => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.update_failed'),
            ];
        }
    }

    /**
     * System automatically cancels a booking due to unpaid deposit/grace period expiration.
     *
     * @param int $id
     * @param string $reason
     * @return array{success: bool, data: mixed, message: string}
     */
    public function handleSystemCancelBooking(int $id, string $reason): array
    {
        try {
            DB::beginTransaction();

            // Acquire lock on the booking record
            $booking = Booking::lockForUpdate()->find($id);

            if (!$booking) {
                DB::rollBack();
                return ['success' => false, 'data' => null, 'message' => 'Booking not found'];
            }

            if ((int) $booking->status === BookingStatus::CANCELLED->value) {
                DB::rollBack();
                return ['success' => true, 'data' => $booking, 'message' => 'Already cancelled'];
            }

            $fromStatus = $this->mapStatusName((int) $booking->status);
            $now = Carbon::now();

            // Release the room (free the room status)
            $this->roomsRepository->update($booking->room_id, ['status' => true]);

            $this->bookingRepository->update($id, [
                'status'              => BookingStatus::CANCELLED->value,
                'cancelled_at'        => $now,
                'cancellation_reason' => $reason,
                'deposit_status'      => 'expired_cancelled',
            ]);

            $bookingUpdate = $this->bookingRepository->find($id);

            $this->timelineService->recordCancelled($id, $reason, $fromStatus, null, [
                'cancelled_at' => $now->toIso8601String(),
                'role'         => 'system',
            ]);

            DB::commit();

            // Broadcast Inventory Released event for Channel Manager (T3.3)
            event(new \App\Events\RoomInventoryReleased($booking->room_id));

            // --- Broadcast realtime event ---
            $scope = $this->resolveBroadcastScope($bookingUpdate);
            if ($scope['partner_id'] !== null && $scope['property_id'] !== null) {
                $this->safeDispatch(
                    'booking.cancelled',
                    static function () use ($bookingUpdate, $scope, $reason): void {
                        BookingCancelled::dispatch(
                            $bookingUpdate,
                            $scope['partner_id'],
                            $scope['property_id'],
                            null,
                            $reason,
                        );
                    },
                );
            }

            // --- Send cancellation email to guest (after commit) ---
            $bookingWithRelations = Booking::with(['user', 'room.property'])->find($id);
            $guest     = $bookingWithRelations?->user;
            $guestRoom = $bookingWithRelations?->room;
            if ($guest && $guest->email) {
                $startCarbon = Carbon::parse($bookingUpdate->start_date);
                $endCarbon   = Carbon::parse($bookingUpdate->end_date);
                $cancelledAt = Carbon::parse($bookingUpdate->cancelled_at)
                    ->timezone('Asia/Ho_Chi_Minh')
                    ->format('d/m/Y H:i:s');

                $cancelEmailData = [
                    'booking_code'        => (string) ($bookingUpdate->booking_code ?? ''),
                    'booking_created_at'  => Carbon::parse($bookingUpdate->created_at)
                        ->timezone('Asia/Ho_Chi_Minh')
                        ->format('d/m/Y H:i:s'),
                    'room_title'          => (string) ($guestRoom?->title ?? ''),
                    'property_name'       => (string) ($guestRoom?->property?->name ?? ''),
                    'property_address'    => (string) ($guestRoom?->property?->address_detail ?? ''),
                    'start_date'          => $startCarbon->format('d/m/Y'),
                    'end_date'            => $endCarbon->format('d/m/Y'),
                    'cancelled_at'        => $cancelledAt,
                    'cancellation_reason' => $reason,
                    'bookings_url'        => config('app.url_frontend') . '/bks-stay/bookings/' . $bookingUpdate->id,
                    'room_url'            => config('app.url_frontend') . '/rooms/' . $bookingUpdate->room_id,
                    'goline_phone'       => '0243 795 7250',
                ];

                SendBookingCancelled::dispatch($guest->email, $guest->name ?? $guest->email, $cancelEmailData);
            }

            return [
                'success' => true,
                'data'    => $bookingUpdate,
                'message' => 'System cancelled successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('System booking cancellation failed', [
                'booking_id' => $id,
                'error'       => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data'    => null,
                'message' => 'System cancellation failed',
            ];
        }
    }

    /**
     * Resolve partner_id (property owner) + property_id (property id) cho
     * broadcast scope. Trả null cho mỗi field nếu không xác định được —
     * caller chịu trách nhiệm bỏ qua việc broadcast khi thiếu scope.
     *
     * @param Booking $booking
     * @return array{partner_id: int|null, property_id: int|null}
     */
    private function resolveBroadcastScope(Booking $booking): array
    {
        $room = $this->roomsRepository->find($booking->room_id);
        if (! $room) {
            return ['partner_id' => null, 'property_id' => null];
        }

        $property = $room->property;
        if (! $property) {
            return ['partner_id' => null, 'property_id' => null];
        }

        return [
            'partner_id'  => (int) $property->user_id,
            'property_id' => (int) $property->id,
        ];
    }

    private function bootstrapChatConversation(Booking $booking): void
    {
        try {
            $this->chatService->bootstrapFromBooking($booking);
        } catch (Throwable $e) {
            Log::warning('Chat conversation bootstrap failed', [
                'booking_id' => $booking->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispatch broadcast event với try/catch để lỗi network/queue không phá
     * flow nghiệp vụ (booking đã commit). Lỗi chỉ ghi log warning để ops
     * theo dõi qua Sentry/Logstash.
     */
    private function safeDispatch(string $eventName, callable $factory): void
    {
        try {
            $factory();
        } catch (Throwable $e) {
            Log::warning('Broadcast dispatch failed', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Translate a numeric booking status to the canonical string used in
     * timeline events (kept private because it is an internal mapping
     * concern).
     */
    private function mapStatusName(int $status): string
    {
        return match ($status) {
            BookingStatus::PENDING->value                 => BookingTimelineService::STATUS_PENDING,
            BookingStatus::CONFIRMED->value               => BookingTimelineService::STATUS_CONFIRMED,
            BookingStatus::CANCELLED->value               => BookingTimelineService::STATUS_CANCELLED,
            BookingStatus::COMPLETED->value               => BookingTimelineService::STATUS_COMPLETED,
            BookingStatus::PENDING_CANCELLATION->value    => BookingTimelineService::STATUS_PENDING_CANCELLATION,
            default                                       => 'unknown',
        };
    }

    /**
     * Confirm booking.
     *
     * Records `confirmed_at` for KPI calculation, appends a timeline event,
     * and (in the same transaction) generates the contract document. The
     * method is idempotent: confirming an already-confirmed booking returns
     * a clear message without creating a second contract.
     *
     * Phase 3 wires `ConflictChecker` with pessimistic `lockForUpdate` to
     * prevent overbooking in face of concurrent confirms or interleaving
     * room blocks. When a conflict is detected the response carries a
     * `BOOKING_CONFLICT` code plus the conflicting bookings/blocks so the
     * Partner UI can highlight them.
     *
     * @param Request $request
     * @param int $id
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    public function handleConfirmBooking(Request $request, int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);

            if (!$booking) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.not_found'),
                ];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể xác nhận.',
                ];
            }

            $checkUser = $this->bookingRepository->checkUser($request);
            if (!$checkUser) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized'),
                ];
            }

            if ((int) $booking->status === BookingStatus::CONFIRMED->value) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.already_confirmed'),
                ];
            }

            if ((int) $booking->status === BookingStatus::CANCELLED->value) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.already_cancelled'),
                ];
            }

            if ((int) $booking->status === BookingStatus::PENDING_CANCELLATION->value) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.confirm_blocked_pending_cancellation'),
                ];
            }

            DB::beginTransaction();

            $startDate = (string) $booking->getRawOriginal('start_date');
            $endDate   = (string) $booking->getRawOriginal('end_date');
            $conflicts = $this->conflictChecker->findConflicts(
                (int) $booking->room_id,
                $startDate,
                $endDate,
                excludeBookingId: (int) $id,
                useLock: true,
            );

            if ($conflicts['hasConflict']) {
                DB::rollBack();
                $this->timelineService->recordConflictDetected($id, null, [
                    'operation'               => 'confirm',
                    'conflicting_booking_ids' => $conflicts['bookings']->pluck('id')->all(),
                    'conflicting_block_ids'   => $conflicts['blocks']->pluck('id')->all(),
                ]);

                return [
                    'success' => false,
                    'data'    => [
                        'bookings' => $conflicts['bookings']->map(fn ($b) => [
                            'id'         => (int) $b->id,
                            'start_date' => (string) $b->getRawOriginal('start_date'),
                            'end_date'   => (string) $b->getRawOriginal('end_date'),
                            'status'     => (int) $b->status,
                        ])->values()->all(),
                        'blocks'   => $conflicts['blocks']->map(fn ($block) => [
                            'id'         => (int) $block->id,
                            'block_type' => (string) $block->block_type,
                            'start_date' => (string) $block->getRawOriginal('start_date'),
                            'end_date'   => (string) $block->getRawOriginal('end_date'),
                        ])->values()->all(),
                    ],
                    'message' => __('booking.messages.confirm_conflict'),
                    'code'    => 'BOOKING_CONFLICT',
                ];
            }

            $now = Carbon::now();
            $paymentStatus = PaymentStatus::UNPAID->value;
            if ($booking->payment_method === 'online') {
                $paymentStatus = PaymentStatus::PAID->value;
            } elseif ($booking->deposit_amount > 0 && $booking->deposit_status === 'confirmed_by_partner') {
                $paymentStatus = PaymentStatus::PARTIALLY_PAID->value;
            }

            $this->bookingRepository->update($id, [
                'status'         => BookingStatus::CONFIRMED->value,
                'confirmed_at'   => $now,
                'payment_status' => $paymentStatus,
            ]);
            $updated = $this->bookingRepository->find($id);

            // Sinh hợp đồng (Generate Contract)
            $this->createContractDocumentForBooking($updated, $now);

            $contractType = null;
            $firstContract = $updated->contracts()->orderBy('id')->first();
            if ($firstContract instanceof Contract) {
                $contractType = (string) ($firstContract->contract_type ?? '');
            }

            $this->timelineService->recordConfirmed($id, null, [
                'confirmed_at'  => $now->toIso8601String(),
                'contract_type' => $contractType !== '' ? $contractType : null,
            ]);

            DB::commit();

            $scope = $this->resolveBroadcastScope($updated);
            if ($scope['partner_id'] !== null && $scope['property_id'] !== null) {
                $actorId = Auth::id();
                $this->safeDispatch('booking.confirmed', static function () use ($updated, $scope, $actorId): void {
                    BookingConfirmed::dispatch(
                        $updated,
                        $scope['partner_id'],
                        $scope['property_id'],
                        $actorId,
                    );
                });
            }

            return [
                'success' => true,
                'data'    => $updated,
                'message' => __('booking.messages.confirmed_successfully'),
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error(__('booking.messages.update_failed'), [
                'booking_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.update_failed'),
            ];
        }
    }

    /**
     * Bulk confirm bookings. Each booking is processed independently through
     * the single-confirm path, which keeps existing authorization, conflict
     * lock, timeline and broadcast behavior intact.
     *
     * @param Request $request
     * @param array<int, int|string> $ids
     * @return array{
     *     success: bool,
     *     data: array{succeeded: array<int, int>, failed: array<int, array{id: int, reason: string, code?: string}>},
     *     message: string
     * }
     */
    public function handleBulkConfirm(Request $request, array $ids): array
    {
        return $this->processBulkAction(
            $request,
            $ids,
            fn (Request $itemRequest, int $id): array => $this->handleConfirmBooking($itemRequest, $id),
            __('booking.messages.bulk_confirm_completed'),
        );
    }

    /**
     * Bulk cancel bookings with a shared reason. Each booking is processed
     * independently so one failure never rolls back another booking.
     *
     * @param Request $request
     * @param array<int, int|string> $ids
     * @param string $reason
     * @return array{
     *     success: bool,
     *     data: array{succeeded: array<int, int>, failed: array<int, array{id: int, reason: string, code?: string}>},
     *     message: string
     * }
     */
    public function handleBulkCancel(Request $request, array $ids, string $reason): array
    {
        return $this->processBulkAction(
            $request,
            $ids,
            function (Request $itemRequest, int $id) use ($reason): array {
                $itemRequest->merge(['reason' => $reason]);

                return $this->handleCancelBooking($itemRequest, $id);
            },
            __('booking.messages.bulk_cancel_completed'),
        );
    }

    /**
     * @param Request $request
     * @param array<int, int|string> $ids
     * @param callable(Request, int): array{success: bool, data: mixed, message: string, code?: string} $action
     * @param string $message
     * @return array{
     *     success: bool,
     *     data: array{succeeded: array<int, int>, failed: array<int, array{id: int, reason: string, code?: string}>},
     *     message: string
     * }
     */
    private function processBulkAction(Request $request, array $ids, callable $action, string $message): array
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $rawId) {
            $id = (int) $rawId;
            $itemRequest = clone $request;
            $itemRequest->merge(['id' => $id]);

            $result = $action($itemRequest, $id);
            if ($result['success']) {
                $succeeded[] = $id;
                continue;
            }

            $failure = [
                'id'     => $id,
                'reason' => (string) $result['message'],
            ];

            if (isset($result['code'])) {
                $failure['code'] = (string) $result['code'];
            }

            $failed[] = $failure;
        }

        return [
            'success' => true,
            'data'    => [
                'succeeded' => $succeeded,
                'failed'    => $failed,
            ],
            'message' => $message,
        ];
    }

    /**
     * Update booking (admin): start_date, end_date, status
     * Business rules reference confirm/cancel logic
     *
     * @param Request $request
     * @param int $id
     * @return array{success: bool, data: Booking|null, message: string}
     */
    public function handleUpdateBooking(Request $request, int $id): array
    {
        try {
            // check user authorization
            $checkUser = $this->bookingRepository->checkUser($request);
            if (!$checkUser) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized_staff_action'),
                ];
            }

            $booking = $this->bookingRepository->find($id);
            if (!$booking) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.not_found'),
                ];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể chỉnh sửa.',
                ];
            }

            $updated = $this->bookingRepository->update(
                $id,
                $request->only(['start_date', 'end_date', 'status'])
            );
            return [
                'success' => true,
                'data'    => $updated,
                'message' => __('booking.messages.updated_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error(__('booking.messages.update_failed'), [
                'booking_id' => $id,
                'data'        => [],
                'error'      => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.update_failed'),
            ];
        }
    }

    /**
     * Destroy booking (admin)
     *
     * @param int $id
     * @return array{success: bool, data: null, message: string}
     */
    public function handleDestroyBooking(int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);
            if (! $booking) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.not_found'),
                ];
            }

            DB::beginTransaction();
            // If booking was confirmed, free the room
            if ((int) $booking->status === BookingStatus::CONFIRMED->value) {
                // Free the room when deleting a confirmed booking
                $this->roomsRepository->update($booking->room_id, ['status' => true]);
            }

            $deleted = $this->bookingRepository->delete($id);
            DB::commit();

            if (! $deleted) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.delete_failed'),
                ];
            }

            return [
                'success' => true,
                'data'    => null,
                'message' => __('booking.messages.deleted_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(__('booking.messages.delete_failed'), [
                'booking_id' => $id,
                'error'       => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.delete_failed'),
            ];
        }
    }

    /**
     * ============================================
     * USER API
     * ============================================
     */

    /**
     * User create booking for a specific room
     *
     * @param Request $request
     * @param int $roomId
     * @return array
     */
    public function handleUserCreateBooking(Request $request, int $roomId): array
    {
        try {
            DB::beginTransaction();

            $token = Str::random(20) . time();
            $email = $request->input('email');

            // check if user exists
            $user = $this->usersRepository->findOneBy(['email' => $email], false);
            // create new user only if user doesn't exist
            if ($user) {
                $createUser = $user;
                // If user exists but is not verified, regenerate token so they can set password and activate account
                if (!$createUser->is_email_verified) {
                    $this->usersRepository->update($createUser->id, [
                        'verification_token' => $token,
                        'token_expires_at'   => Carbon::now()->addMinutes(
                            config('const.TIME_TOKEN_CHECK_VERIFY_EMAIL', 1440)
                        ),
                    ]);
                    $createUser = $this->usersRepository->find($createUser->id);
                }
            } else {
                $createUser = $this->usersRepository->create([
                    'name'               => $request->input('name'),
                    'email'              => $email,
                    'phone'              => $request->input('phone'),
                    'password'           => bcrypt(Str::random(16)),
                    'role'               => UserType::USER,
                    'status'             => EnumsStatus::PENDING->value,
                    'verification_token' => $token,
                    'is_email_verified'  => 0,
                    'token_expires_at'   => Carbon::now()->addMinutes(
                        config('const.TIME_TOKEN_CHECK_VERIFY_EMAIL')
                    ),
                ]);
            }

            $this->usersRepository->update($createUser->id, [
                'created_by' => $createUser->id,
                'updated_by' => $createUser->id,
            ]);

            // check for room conflict
            $existRoom = $this->bookingRepository->checkRoomConflict(
                $roomId,
                $request->input('start_date'),
                $request->input('end_date'),
            );

            throw_if(
                $existRoom,
                Exception::class,
                __('booking.messages.room_unavailable')
            );

            $startDateInput = (string) $request->input('start_date');
            $endDateInput = (string) $request->input('end_date');

            $roomPrice = null;
            $requestedPriceId = $request->input('price_id');
            if ($requestedPriceId !== null && $requestedPriceId !== '') {
                $roomPrice = RoomPrice::query()
                    ->where('id', (int) $requestedPriceId)
                    ->where('room_id', $roomId)
                    ->first();
            }
            if ($roomPrice === null) {
                $resolvedPriceId = BookingStayAmountCalculator::resolveRoomPriceIdForStay(
                    $roomId,
                    $startDateInput,
                    $endDateInput,
                );
                $roomPrice = $resolvedPriceId !== null
                    ? RoomPrice::query()->find($resolvedPriceId)
                    : null;
            }

            $roomModel = $this->roomsRepository->find($roomId);
            if (!$roomModel) {
                throw new \Exception("Room not found");
            }

            // Check minimum stay requirement
            if ($roomPrice !== null && $roomPrice->minimum_stay > 0) {
                $unit = strtolower(trim((string) $roomPrice->unit));
                $nights = BookingStayAmountCalculator::countStayNights($startDateInput, $endDateInput);

                $minNights = (int) $roomPrice->minimum_stay;
                if ($unit === 'month') {
                    $minNights = (int) $roomPrice->minimum_stay * 30;
                }

                if ($nights < $minNights) {
                    $unitLabel = $unit === 'month' ? 'tháng' : 'đêm';
                    throw new \Exception("Thời gian lưu trú tối thiểu cho gói này là {$roomPrice->minimum_stay} {$unitLabel} ({$minNights} ngày). Quý khách đã chọn {$nights} đêm.");
                }
            }

            // Check dynamic deposit requirements
            $depositPolicy = $this->policyService->calculateRequiredDeposit(
                $roomModel,
                $roomPrice,
                $startDateInput,
                $endDateInput
            );

            $paymentMethod = $request->input('payment_method');

            if ($depositPolicy['required'] && $depositPolicy['amount'] > 0) {
                if ($paymentMethod === 'pay_at_counter') {
                    throw new \Exception("Tùy chọn thanh toán tại quầy không khả dụng vì phòng yêu cầu đặt cọc trong khoảng thời gian này.");
                }
            }

            // create booking
            $booking = $this->bookingRepository->create([
                'user_id'        => $createUser->id,
                'room_id'        => $roomId,
                'start_date'     => $startDateInput,
                'end_date'       => $endDateInput,
                'price_id'       => $roomPrice?->id,
                'note'           => $request->input('note'),
                'status'         => BookingStatus::PENDING->value,
                'created_by'     => $createUser->id,
                'payment_method' => $paymentMethod,
            ]);

            // create link between booking and service table
            // auto insert into booking_services with booking_id and service_ids
            $serviceIds = $request->input('service_ids', []);
            if (!empty($serviceIds)) {
                $booking->services()->attach($serviceIds, [
                    'created_by' => $createUser->id,
                    'updated_by' => $createUser->id,
                ]);
            }

            $bookingCode = sprintf('RM-%04d-%06d', (int) date('Y'), (int) $booking->id);
            $booking->update(['booking_code' => $bookingCode]);
            $booking->refresh();

            // Create deposit if required by policy
            $this->depositService->createDeposit($booking);

            // prepare room to send mail
            $room = $this->roomsRepository->getRoomInfoSendMail($roomId);

            // calculate total amount
            $startDate = Carbon::parse($startDateInput);
            $endDate = Carbon::parse($endDateInput);
            if ($roomPrice === null && $booking->price_id) {
                $roomPrice = RoomPrice::query()->find($booking->price_id);
            }
            $totalDays = BookingStayAmountCalculator::countStayDays(
                $startDate->toDateString(),
                $endDate->toDateString(),
            );
            $roomStayTotal = BookingStayAmountCalculator::computeRoomStayTotalForRoomPrice(
                $startDate->toDateString(),
                $endDate->toDateString(),
                $roomPrice,
            );

            // Format services for email template (from selected services in booking)
            $selectedServices = $booking->services()->select('name', 'price')->get();
            $servicesTotal = (float) $selectedServices->sum(fn ($service) => (float) ($service->price ?? 0));
            $services = $selectedServices->map(fn ($service) => [
                'name'   => $service->name,
                'amount' => (float) ($service->price ?? 0),
            ])->toArray();

            $grandTotal = round($roomStayTotal + $servicesTotal, 2);

            $responseData = [
                'booking_id'         => (int) $booking->id,
                'booking_code'       => (string) $booking->booking_code,
                'user_id'            => (int) $createUser->id,
                'status'             => (int) $booking->status,
                'start_date'         => $startDate->format('Y-m-d'),
                'end_date'           => $endDate->format('Y-m-d'),
                'room_id'            => (int) $booking->room_id,
                'price_id'           => (int) $booking->price_id,
                'total_amount'       => $grandTotal,
                'room_stay_amount'   => $roomStayTotal,
                'services_total'     => $servicesTotal,
                'unit_price'         => (float) ($roomPrice?->price ?? 0),
                'price_unit'         => (string) ($roomPrice?->unit ?? 'night'),
                'room_title'         => (string) ($room->title ?? ''),
                'property_address'   => (string) ($room->property_address ?? ''),
            ];

            if ($paymentMethod === 'online') {
                $responseData['payment_url'] = url('/api/v1/payments/checkout?booking_id=' . $booking->id);
            }

            $emailInfo = [
                'booking_code'       => $bookingCode,
                'booking_created_at' => Carbon::parse($booking->created_at)
                    ->timezone('Asia/Ho_Chi_Minh')
                    ->format('d/m/Y H:i:s'),
                'room_title'         => $room->title,
                'room_description'   => $room->description,
                'room_deposit'       => $room->deposit ?? 0,
                'amenities'          => $room->amenities ?? [],
                'services'           => $services,
                'room_url'           => config('app.url_frontend') . '/rooms/' . $roomId,
                'bookings_url'       => config('app.url_frontend') . '/bks-stay/bookings/' . $booking->id,
                'is_first_time'      => ($user && $user->is_email_verified) ? false : true,
                'company_name'       => $room->company_name ?? '',
                'company_phone'      => $room->company_phone ?? '',
                'partner_address'    => $room->address ?? '',
                'property_name'      => $room->property_name ?? '',
                'property_address'   => $room->property_address ?? '',
                'start_time'         => $startDate->format('d/m/Y'),
                'end_time'           => $endDate->format('d/m/Y'),
                'total_days'         => $totalDays,
                'room_stay_amount'   => $roomStayTotal,
                'services_total'     => $servicesTotal,
                'unit_price'         => (float) ($roomPrice?->price ?? 0),
                'price_unit'         => (string) ($roomPrice?->unit ?? 'night'),
                'deposit_deadline'   => $this->computeDepositDeadline($startDate, Carbon::now()),
                'cancellation_policy' => $this->formatCancellationPolicyForEmail($booking),
                'total_amount'       => $grandTotal,
                'goline_phone'       => '0243 795 7250',
                'token'              => $token,
            ];
            DB::commit();

            $this->bootstrapChatConversation($booking);

            // Realtime: broadcast booking mới đến partner sở hữu room (Phase 2).
            $scope = $this->resolveBroadcastScope($booking);
            if ($scope['partner_id'] !== null && $scope['property_id'] !== null) {
                $this->safeDispatch('booking.created', static function () use ($booking, $scope): void {
                    BookingCreated::dispatch($booking, $scope['partner_id'], $scope['property_id']);
                });
            }

            // Send mail AFTER commit success
            SendBooking::dispatch($createUser->email, $createUser->name, $emailInfo);

            return [
                'success' => true,
                'data'    => $responseData,
                'message' => __('booking.messages.user_booking_created_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('User booking creation failed: ' . $e->getMessage(), [
                'email'   => $request->input('email'),
                'room_id' => $roomId,
                'trace'   => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Public read-only lookup by booker email + booking code (sent by email).
     *
     * @param Request $request
     * @return array{success: bool, data: array<string, mixed>|null, message: string}
     */
    public function handlePublicBookingLookup(Request $request): array
    {
        try {
            $email = mb_strtolower(trim((string) $request->input('email')));
            $rawCode = preg_replace('/\s+/', '', (string) $request->input('booking_code'));

            $booking = Booking::query()
                ->with(['user', 'room.property', 'services', 'price'])
                ->whereRaw('LOWER(booking_code) = ?', [mb_strtolower($rawCode)])
                ->first();

            if ($booking === null || $booking->user === null) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.lookup_not_found'),
                ];
            }

            if (mb_strtolower((string) $booking->user->email) !== $email) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.lookup_not_found'),
                ];
            }

            $roomStayTotal = $this->computeRoomStayTotalForBooking($booking);
            $servicesTotal = $this->computeServicesTotalForBooking($booking);

            return [
                'success' => true,
                'data'    => $this->buildPublicBookingLookupPayload($booking, $roomStayTotal, $servicesTotal),
                'message' => __('booking.messages.lookup_retrieved'),
            ];
        } catch (Throwable $e) {
            Log::error('Public booking lookup failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.lookup_not_found'),
            ];
        }
    }

    /**
     * Public update email of a pending booking when the user entered wrong email.
     *
     * @param Request $request
     * @return array{success: bool, message: string}
     */
    public function handlePublicUpdateBookingEmail(Request $request): array
    {
        try {
            DB::beginTransaction();

            $oldEmail = mb_strtolower(trim((string) $request->input('old_email')));
            $newEmail = mb_strtolower(trim((string) $request->input('new_email')));
            $rawCode  = preg_replace('/\s+/', '', (string) $request->input('booking_code'));

            $booking = Booking::query()
                ->with(['user', 'room.property', 'services', 'price'])
                ->whereRaw('LOWER(booking_code) = ?', [mb_strtolower($rawCode)])
                ->first();

            if ($booking === null || $booking->user === null) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy đặt phòng tương ứng.',
                ];
            }

            // Only allow email updates for PENDING bookings
            if ((int) $booking->status !== \App\Enums\BookingStatus::PENDING->value) {
                return [
                    'success' => false,
                    'message' => 'Không thể thay đổi email do đơn đặt phòng này đã được xác nhận hoặc hủy bỏ.',
                ];
            }

            if (mb_strtolower((string) $booking->user->email) !== $oldEmail) {
                return [
                    'success' => false,
                    'message' => 'Email hiện tại của đơn đặt phòng không khớp với thông tin cung cấp.',
                ];
            }

            // Check if the user is a pending user (not verified yet)
            $oldUser = $booking->user;
            $token = Str::random(20) . time();

            // Look up if a user already exists with the new email
            $newUser = $this->usersRepository->findOneBy(['email' => $newEmail], false);

            if ($newUser) {
                // Link booking to the existing user
                $booking->update(['user_id' => $newUser->id]);
                // If user exists but is not verified, regenerate token so they can set password and activate account
                if (!$newUser->is_email_verified) {
                    $this->usersRepository->update($newUser->id, [
                        'verification_token' => $token,
                        'token_expires_at'   => Carbon::now()->addMinutes(
                            config('const.TIME_TOKEN_CHECK_VERIFY_EMAIL', 1440)
                        ),
                    ]);
                    $newUser = $this->usersRepository->find($newUser->id);
                }
            } else {
                // Create a new pending user for the correct email
                $newUser = $this->usersRepository->create([
                    'name'               => $oldUser->name,
                    'email'              => $newEmail,
                    'phone'              => $oldUser->phone,
                    'password'           => bcrypt(Str::random(16)),
                    'role'               => \App\Enums\UserType::USER,
                    'status'             => \App\Enums\Status::PENDING->value,
                    'verification_token' => $token,
                    'is_email_verified'  => 0,
                    'token_expires_at'   => Carbon::now()->addMinutes(
                        config('const.TIME_TOKEN_CHECK_VERIFY_EMAIL', 1440)
                    ),
                ]);

                $this->usersRepository->update($newUser->id, [
                    'created_by' => $newUser->id,
                    'updated_by' => $newUser->id,
                ]);

                $booking->update(['user_id' => $newUser->id]);
            }

            $booking->refresh();

            // If the old user was a pending guest user (unverified) and has no other bookings, we clean it up
            $hasOtherBookings = Booking::query()->where('user_id', $oldUser->id)->where('id', '!=', $booking->id)->exists();
            if (!$oldUser->is_email_verified && !$hasOtherBookings) {
                $oldUser->delete();
            }

            // Prepare email information for resend
            $room = $this->roomsRepository->getRoomInfoSendMail($booking->room_id);
            $roomPrice = $booking->price;
            $totalDays = BookingStayAmountCalculator::countStayDays(
                $booking->start_date,
                $booking->end_date
            );
            $roomStayTotal = $this->computeRoomStayTotalForBooking($booking);
            $servicesTotal = $this->computeServicesTotalForBooking($booking);
            $grandTotal = round($roomStayTotal + $servicesTotal, 2);

            $selectedServices = $booking->services()->select('name', 'price')->get();
            $services = $selectedServices->map(fn ($service) => [
                'name'   => $service->name,
                'amount' => (float) ($service->price ?? 0),
            ])->toArray();

            $emailInfo = [
                'booking_id'         => (int) $booking->id,
                'booking_code'       => (string) $booking->booking_code,
                'room_title'         => (string) ($room->title ?? ''),
                'property_name'      => (string) ($room->property_name ?? ''),
                'property_address'   => (string) ($room->property_address ?? ''),
                'start_time'         => Carbon::parse($booking->start_date)->format('d/m/Y'),
                'end_time'           => Carbon::parse($booking->end_date)->format('d/m/Y'),
                'total_days'         => $totalDays,
                'room_stay_amount'   => $roomStayTotal,
                'services_total'     => $servicesTotal,
                'unit_price'         => (float) ($roomPrice?->price ?? 0),
                'price_unit'         => (string) ($roomPrice?->unit ?? 'night'),
                'deposit_deadline'   => $this->computeDepositDeadline(Carbon::parse($booking->start_date), Carbon::now()),
                'cancellation_policy' => $this->formatCancellationPolicyForEmail($booking),
                'total_amount'       => $grandTotal,
                'goline_phone'       => '0243 795 7250',
                'token'              => $newUser->verification_token,
                'is_first_time'      => (bool) ($newUser->is_email_verified === 0),
                'bookings_url'       => config('app.url_frontend') . '/bks-stay/bookings/' . $booking->id,
                'room_url'           => config('app.url_frontend') . '/rooms/' . $booking->room_id,
            ];

            DB::commit();

            // Send booking email to the new address
            SendBooking::dispatch($newUser->email, $newUser->name, $emailInfo);

            return [
                'success' => true,
                'message' => 'Thay đổi email nhận thông tin đặt phòng thành công. Email kích hoạt mới đã được gửi đi.',
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Public booking email update failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPublicBookingLookupPayload(
        Booking $booking,
        float $roomStayTotal,
        float $servicesTotal
    ): array {
        $booking->loadMissing(['room.property']);
        $startDate = Carbon::parse($booking->start_date);
        $endDate = Carbon::parse($booking->end_date);

        return [
            'booking_id'       => (int) $booking->id,
            'booking_code'     => (string) ($booking->booking_code ?? ''),
            'status'           => (int) $booking->status,
            'start_date'       => $startDate->format('Y-m-d'),
            'end_date'         => $endDate->format('Y-m-d'),
            'room_id'          => (int) $booking->room_id,
            'total_amount'     => round($roomStayTotal + $servicesTotal, 2),
            'room_title'       => (string) ($booking->room?->title ?? ''),
            'property_address' => (string) ($booking->room?->property?->address_detail ?? ''),
            'payment_method'   => (string) ($booking->payment_method ?? 'online'),
            'created_at'       => $booking->getRawOriginal('created_at') ? Carbon::parse($booking->getRawOriginal('created_at'))->toIso8601String() : null,
        ];
    }

    private function computeRoomStayTotalForBooking(Booking $booking): float
    {
        return BookingStayAmountCalculator::computeRoomStayTotalForBooking($booking);
    }

    private function computeServicesTotalForBooking(Booking $booking): float
    {
        return BookingStayAmountCalculator::computeServicesTotalForBooking($booking);
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Ensure a contract exists for a confirmed/completed partner booking.
     * Idempotent — returns the existing contract when already present.
     *
     * @return array{success: bool, data: mixed, message: string}
     */
    public function handleEnsureBookingContract(int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);
            if (!$booking) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.not_found'),
                ];
            }

            $partnerId = (int) Auth::id();
            $booking->loadMissing(['room.property', 'price', 'contracts']);

            if (
                ! $booking->room?->property
                || (int) $booking->room->property->user_id !== $partnerId
            ) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized'),
                ];
            }

            if (! in_array((int) $booking->status, [BookingStatus::CONFIRMED->value, BookingStatus::COMPLETED->value], true)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Chỉ booking đã duyệt hoặc hoàn thành mới có hợp đồng.',
                ];
            }

            $contract = $this->createContractDocumentForBooking($booking);
            if (! $contract instanceof Contract) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Không thể tạo hợp đồng cho booking này.',
                ];
            }

            return [
                'success' => true,
                'data'    => ['id' => (int) $contract->id],
                'message' => 'Hợp đồng đã sẵn sàng.',
            ];
        } catch (Exception $e) {
            Log::error('Ensure booking contract failed: ' . $e->getMessage());

            return [
                'success' => false,
                'data'    => null,
                'message' => 'Không thể tạo hợp đồng cho booking này.',
            ];
        }
    }

    /**
     * Create the default contract document for a booking when missing.
     */
    private function createContractDocumentForBooking(Booking $booking, ?Carbon $signedAt = null): ?Contract
    {
        $existing = $booking->contracts()->orderBy('id')->first();
        if ($existing instanceof Contract) {
            return $existing;
        }

        $room = $booking->relationLoaded('room') && $booking->room
            ? $booking->room
            : $this->roomsRepository->find((int) $booking->room_id);

        if (! $room) {
            return null;
        }

        $room->loadMissing(['property.propertyType']);

        if (! $room->property) {
            return null;
        }

        $propertyType = $room->property->propertyType;
        $propertySlug = $propertyType ? (string) $propertyType->slug : '';
        $priceUnit = (string) ($booking->price?->unit ?? 'night');
        $stayNights = BookingStayAmountCalculator::countStayNights(
            $booking->start_date,
            $booking->end_date,
        );

        $isLongTerm = StayClassificationService::isLongTermLeaseBooking(
            $propertySlug,
            $stayNights,
            $priceUnit,
        );

        $contractType = $isLongTerm ? 'LEASE_AGREEMENT' : 'TERMS_AND_CONDITIONS';
        $contractStatus = $isLongTerm ? 0 : 1;
        $contractTitle = $isLongTerm ? 'Hợp đồng thuê phòng / Căn hộ' : 'Phiếu xác nhận lưu trú';
        $content = 'Hợp đồng cho mã đặt phòng ' . sprintf('RM-%04d-%06d', date('Y'), $booking->id);
        $now = $signedAt ?? Carbon::now();

        return $booking->contracts()->create([
            'title'          => $contractTitle,
            'content'        => $content,
            'status'         => $contractStatus,
            'type'           => 'Rental',
            'contract_type'  => $contractType,
            'created_by'     => Auth::id() ?? 1,
            'signature_date' => $contractStatus === 1 ? $now : null,
        ]);
    }

    /**
     * Get bookings for partner
     *
     * @param Request $request
     * @return array
     */
    public function handleGetAllBookingsForPartner(Request $request): array
    {
        try {
            $partnerId = Auth::id();
            $bookings = $this->bookingRepository->getBookingsForPartner($partnerId, $request);

            return [
                'success' => true,
                'data'    => $bookings,
                'message' => __('booking.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error("Partner get bookings failed: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.retrieved_failed'),
            ];
        }
    }

    /**
     * Get lightweight booking counters for partner booking tabs/cards.
     *
     * @return array
     */
    public function handleGetBookingSummaryForPartner(): array
    {
        try {
            $partnerId = Auth::id();
            $summary = $this->bookingRepository->getBookingSummaryForPartner((int) $partnerId);

            return [
                'success' => true,
                'data'    => $summary,
                'message' => __('booking.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error("Partner get booking summary failed: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.retrieved_failed'),
            ];
        }
    }

    /**
     * Handle Check-in for a booking
     *
     * @param int $id
     * @return array
     */
    public function handleCheckIn(int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);
            if (!$booking) {
                return ['success' => false, 'message' => 'Booking not found'];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể check-in.'
                ];
            }

            if ($booking->status === BookingStatus::PENDING_CANCELLATION->value) {
                return ['success' => false, 'message' => 'Không thể nhận phòng cho đơn đang chờ duyệt hủy'];
            }

            if ($booking->status !== BookingStatus::CONFIRMED->value) {
                return ['success' => false, 'message' => 'Chỉ có thể nhận phòng cho đơn đã duyệt'];
            }

            // [T3.1] Check-in Gate Verification
            // 1. Verify deposit has been paid and confirmed if applicable
            $isPendingDeposit = !in_array(
                $booking->deposit_status,
                ['confirmed_by_partner', 'held_in_escrow'],
                true
            );
            if ($booking->deposit_amount > 0 && $isPendingDeposit) {
                return [
                    'success' => false,
                    'code'    => 'CHECKIN_GATE_FAILED',
                    'message' => 'Không thể Check-in do chưa hoàn tất thanh toán hoặc xác thực đặt cọc.'
                ];
            }

            // 2. Verify Lease Agreement is signed for long term rentals
            $hasUnsignedLease = $booking->contracts()
                ->where('contract_type', 'LEASE_AGREEMENT')
                ->where('status', 0)
                ->exists();
            if ($hasUnsignedLease) {
                return [
                    'success' => false,
                    'code'    => 'CHECKIN_GATE_FAILED',
                    'message' => 'Không thể Check-in do chưa hoàn thành ký kết hợp đồng thuê nhà điện tử.'
                ];
            }

            DB::beginTransaction();
            $booking->update(['stay_status' => 'checked_in']);
            $this->roomsRepository->update($booking->room_id, ['status' => false]);
            $this->timelineService->recordCheckedIn($id);
            DB::commit();

            return ['success' => true, 'message' => 'Check-in successful'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Check-in failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Check-in failed'];
        }
    }

    /**
     * Handle partner confirming deposit manual validation.
     *
     * @param int $id
     * @return array
     */
    public function handleConfirmDeposit(int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);
            if (!$booking) {
                return ['success' => false, 'message' => 'Booking not found'];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể xác thực cọc.'
                ];
            }

            $success = $this->depositService->confirmReceiptByPartner($id);
            if (!$success) {
                return ['success' => false, 'message' => 'Không thể xác thực đặt cọc cho đơn hàng này.'];
            }

            return ['success' => true, 'message' => 'Xác thực đặt cọc thành công.'];
        } catch (Exception $e) {
            Log::error("Confirm deposit failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Xác thực đặt cọc thất bại.'];
        }
    }


    /**
     * Handle Check-out for a booking
     *
     * @param int $id
     * @return array
     */
    public function handleCheckOut(int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);
            if (!$booking) {
                return ['success' => false, 'message' => 'Booking not found'];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể check-out.'
                ];
            }

            DB::beginTransaction();
            $booking->update([
                'stay_status' => 'checked_out',
                'status' => BookingStatus::COMPLETED->value,
            ]);
            $this->roomsRepository->update($booking->room_id, [
                'status' => true,
                'housekeeping_status' => 'dirty',
            ]);
            $this->timelineService->recordCheckedOut($id);
            DB::commit();

            return ['success' => true, 'message' => 'Check-out successful'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Check-out failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Check-out failed'];
        }
    }

    /**
     * Mark a booking as no-show.
     *
     * Eligibility: booking must be CONFIRMED and start_date must be today
     * or earlier (Asia/Ho_Chi_Minh). The booking transitions stay_status to
     * `no_show`, records `no_show_at`, frees the room, and appends a
     * timeline event. Booking status remains CONFIRMED so KPI revenue can
     * still attribute the (now-cancelled) night.
     *
     * @param Request $request
     * @param int $id
     * @return array{success: bool, data: mixed, message: string}
     */
    public function handleNoShow(Request $request, int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);
            if (! $booking) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.not_found'),
                ];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể đánh dấu no-show.',
                ];
            }

            $checkUser = $this->bookingRepository->checkUser($request);
            if (! $checkUser) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized'),
                ];
            }

            if ((int) $booking->status !== BookingStatus::CONFIRMED->value) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.no_show_only_for_confirmed'),
                ];
            }

            $today = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
            $startDate = Carbon::parse($booking->getRawOriginal('start_date'), 'Asia/Ho_Chi_Minh')->startOfDay();
            if ($startDate->greaterThan($today)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.no_show_not_started_yet'),
                ];
            }

            DB::beginTransaction();
            $now = Carbon::now();
            $this->bookingRepository->update($id, [
                'stay_status' => 'no_show',
                'no_show_at'  => $now,
            ]);
            $this->roomsRepository->update($booking->room_id, ['status' => true]);
            $this->timelineService->recordNoShow($id, null, [
                'no_show_at' => $now->toIso8601String(),
            ]);
            DB::commit();

            event(new RoomInventoryReleased($booking->room_id));

            $updated = $this->bookingRepository->find($id);
            $scope = $this->resolveBroadcastScope($updated);
            if ($scope['partner_id'] !== null && $scope['property_id'] !== null) {
                $actorId = Auth::id();
                $this->safeDispatch('booking.no_show', static function () use ($updated, $scope, $actorId): void {
                    BookingNoShow::dispatch(
                        $updated,
                        $scope['partner_id'],
                        $scope['property_id'],
                        $actorId,
                    );
                });
            }

            return [
                'success' => true,
                'data'    => $updated,
                'message' => __('booking.messages.no_show_successfully'),
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('No-show update failed', [
                'booking_id' => $id,
                'error'       => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.update_failed'),
            ];
        }
    }

    /**
     * Move a booking to a new date range and/or room.
     *
     * Phục vụ FE drag-drop trên Calendar (Phase 3 - T3.15). Áp dụng cho
     * booking đang ở trạng thái PENDING hoặc CONFIRMED. Nếu khoảng mới /
     * phòng mới conflict với booking khác hoặc room_block → trả 409 với
     * payload chi tiết để FE revert.
     *
     * Chỉ partner sở hữu booking được phép move (kiểm bằng `checkUser`).
     * Khi đổi `room_id`, phòng mới cũng phải thuộc partner đăng nhập —
     * `checkUser` validate điều này thông qua join `properties`.
     *
     * @param Request $request Body: start_date, end_date, room_id (tuỳ chọn)
     * @param int $id
     * @return array{success: bool, data: mixed, message: string, code?: string}
     */
    public function handleMove(Request $request, int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);
            if (! $booking) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.not_found'),
                ];
            }

            if ($this->isBookingLocked($booking)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Đơn đặt phòng đã được chốt đối soát tài chính và khóa, không thể di chuyển.',
                ];
            }

            $checkUser = $this->bookingRepository->checkUser($request);
            if (! $checkUser) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized'),
                ];
            }

            $currentStatus = (int) $booking->status;
            if (! in_array($currentStatus, [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value], true)) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.move_only_for_active'),
                ];
            }

            $newStart = (string) $request->input('start_date', $booking->getRawOriginal('start_date'));
            $newEnd   = (string) $request->input('end_date', $booking->getRawOriginal('end_date'));
            $newRoomId = $request->filled('room_id')
                ? (int) $request->input('room_id')
                : (int) $booking->room_id;

            if ($newStart > $newEnd) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.validation.end_date.after_or_equal'),
                ];
            }

            DB::beginTransaction();

            $conflicts = $this->conflictChecker->findConflicts(
                $newRoomId,
                $newStart,
                $newEnd,
                excludeBookingId: (int) $id,
                useLock: true,
            );

            if ($conflicts['hasConflict']) {
                DB::rollBack();
                $this->timelineService->recordConflictDetected($id, null, [
                    'operation'               => 'move',
                    'conflicting_booking_ids' => $conflicts['bookings']->pluck('id')->all(),
                    'conflicting_block_ids'   => $conflicts['blocks']->pluck('id')->all(),
                ]);

                return [
                    'success' => false,
                    'data'    => [
                        'bookings' => $conflicts['bookings']->map(fn ($b) => [
                            'id'         => (int) $b->id,
                            'start_date' => (string) $b->getRawOriginal('start_date'),
                            'end_date'   => (string) $b->getRawOriginal('end_date'),
                            'status'     => (int) $b->status,
                        ])->values()->all(),
                        'blocks'   => $conflicts['blocks']->map(fn ($block) => [
                            'id'         => (int) $block->id,
                            'block_type' => (string) $block->block_type,
                            'start_date' => (string) $block->getRawOriginal('start_date'),
                            'end_date'   => (string) $block->getRawOriginal('end_date'),
                        ])->values()->all(),
                    ],
                    'message' => __('booking.messages.move_conflict'),
                    'code'    => 'BOOKING_CONFLICT',
                ];
            }

            $this->bookingRepository->update($id, [
                'start_date' => $newStart,
                'end_date'   => $newEnd,
                'room_id'    => $newRoomId,
            ]);

            DB::commit();

            $updated = $this->bookingRepository->find($id);

            return [
                'success' => true,
                'data'    => $updated,
                'message' => __('booking.messages.moved_successfully'),
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Booking move failed', [
                'booking_id' => $id,
                'error'      => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'data'    => null,
                'message' => __('booking.messages.update_failed'),
            ];
        }
    }

    /**
     * Kiểm tra xem đơn đặt phòng có bị khóa đối soát hay không.
     *
     * @param \App\Models\Booking $booking
     * @return bool
     */
    private function isBookingLocked(Booking $booking): bool
    {
        if ($booking->settlement_period_id === null) {
            return false;
        }

        $booking->loadMissing('settlementPeriod');
        $period = $booking->settlementPeriod;

        if (!$period) {
            return false;
        }

        $allowedStatuses = [
            PartnerSettlementPeriod::STATUS_DRAFT,
            PartnerSettlementPeriod::STATUS_DISPUTED,
        ];
        return !in_array($period->status, $allowedStatuses, true);
    }

    /**
     * Tính deadline nộp cọc theo grace period.
     *
     * Logic (đồng bộ với FE BookingDetail.tsx line 216):
     *   - Nếu ngày check-in cách thời điểm tạo booking <= 48h → grace 2 giờ
     *   - Còn lại → grace 12 giờ
     */
    private function computeDepositDeadline(Carbon $startDate, Carbon $createdAt): string
    {
        $diffHours = $createdAt->diffInHours($startDate, false);
        $graceHours = $diffHours <= 48 ? 2 : 12;

        return $createdAt->copy()
            ->addHours($graceHours)
            ->timezone('Asia/Ho_Chi_Minh')
            ->format('H:i \n\g\à\y d/m/Y');
    }

    /**
     * Tạo đoạn mô tả chính sách hủy phòng dạng HTML để nhúng vào email.
     *
     * Lấy tất cả tiers theo stay_kind (short/long) từ version hiện hành,
     * format thành bảng HTML đơn giản.
     */
    private function formatCancellationPolicyForEmail(Booking $booking): string
    {
        $version  = (string) config('bcp.baseline_policy_version', '2026-baseline-v1');
        $tz       = (string) config('app.timezone', 'Asia/Ho_Chi_Minh');
        $longMin  = (int) config('bcp.long_stay_min_nights', 30);

        $start = Carbon::parse((string) $booking->start_date, $tz);
        $end   = Carbon::parse((string) $booking->end_date, $tz);

        $stayKind = \App\Support\Bcp\CancellationPolicyTierMatcher::stayKind($start, $end, $longMin, $tz);

        $tiers = \App\Models\CancellationPolicyTier::query()
            ->where('version', $version)
            ->where('stay_kind', $stayKind)
            ->orderByDesc('hours_before_checkin_min')
            ->get();

        if ($tiers->isEmpty()) {
            return '<p style="color:#6b7280;font-size:13px;">' .
                'Vui lòng liên hệ hỗ trợ để biết chi tiết chính sách hủy phòng.</p>';
        }

        $ref = $start->copy()->startOfDay();
        $rows = '';
        foreach ($tiers as $tier) {
            $minH   = (int) $tier->hours_before_checkin_min;
            $maxH   = $tier->hours_before_checkin_max;
            $refund = $tier->refund_percent !== null
                ? number_format((float) $tier->refund_percent, 0) . '%'
                : 'N/A';

            if ($maxH === null) {
                $dt = '00:00 ngày ' . $ref->copy()->subHours($minH)->format('d/m/Y');
                $when = 'Hủy trước ' . $dt . ' (≥ ' . (int)($minH / 24) . ' ngày trước check-in)';
            } elseif ($minH === 0) {
                $dtStart = '00:00 ngày ' . $ref->copy()->subHours($maxH + 1)->format('d/m/Y');
                $when = 'Hủy từ ' . $dtStart . ' trở đi (dưới ' . (int)(($maxH + 1) / 24) . ' ngày trước check-in)';
            } else {
                $dtStart = '00:00 ngày ' . $ref->copy()->subHours($maxH + 1)->format('d/m/Y');
                $dtEnd   = '00:00 ngày ' . $ref->copy()->subHours($minH)->format('d/m/Y');
                $when = 'Hủy từ ' . $dtStart . ' đến trước ' . $dtEnd . ' (' . (int)($minH / 24) . ' đến dưới ' . (int)(($maxH + 1) / 24) . ' ngày trước check-in)';
            }

            $color = ($refund === '100%') ? '#10b981' : '#dc2626';
            $rows .= '<tr style="border-bottom:1px solid #f3f4f6;">'
                . '<td style="padding:8px 12px;font-size:13px;color:#374151;">' . $when . '</td>'
                . '<td style="padding:8px 12px;font-size:13px;font-weight:700;color:'
                . $color . ';text-align:right;">' . $refund . ' hoàn tiền cọc</td>'
                . '</tr>';
        }

        $label = $stayKind === 'long'
            ? 'dài hạn (≥' . $longMin . ' đêm)'
            : 'ngắn hạn';

        return '<p style="font-size:12px;color:#6b7280;margin:0 0 8px 0;">Áp dụng cho lưu trú ' . $label . '</p>'
            . '<table style="width:100%;border-collapse:collapse;background:#fff;">'
            . '<thead><tr style="background:#f9fafb;">'
            . '<th style="padding:8px 12px;font-size:12px;text-align:left;color:#6b7280;">Thời điểm hủy</th>'
            . '<th style="padding:8px 12px;font-size:12px;text-align:right;color:#6b7280;">Hoàn tiền cọc</th>'
            . '</tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>'
            . '<p style="font-size:11px;color:#9ca3af;margin:8px 0 0 0;">'
            . '* Thời gian tính từ 00:00 ngày nhận phòng theo múi giờ Việt Nam (GMT+7).</p>'
            . '<div style="margin-top:12px;padding:12px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;font-size:11px;color:#6b7280;line-height:1.5;font-style:italic;">'
            . '<strong>Lưu ý về quy định hoàn trả:</strong> Các mốc thời gian hoàn cọc trên được thiết lập nhằm đảm bảo sự cân bằng giữa quyền lợi giữ phòng của quý khách và kế hoạch chuẩn bị đón tiếp từ phía chủ nhà. Kính mong quý khách hàng thông cảm và cân nhắc kỹ kế hoạch di chuyển trước khi thực hiện đặt phòng.'
            . '</div>';
    }
}

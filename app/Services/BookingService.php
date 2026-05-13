<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Repositories\BuildingRepository\BuildingsRepositoryInterface;
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
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\BookingNoShow;
use App\Jobs\SendBooking;
use App\Jobs\VerifyMail;
use App\Models\Booking;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\BookingTimelineService;
use App\Services\ConflictChecker;
use Throwable;

final class BookingService
{
    /**
     * Booking repository instance
     */
    protected BookingRepositoryInterface $bookingRepository;
    protected RoomsRepositoryInterface $roomsRepository;
    protected BuildingsRepositoryInterface $buildingsRepository;
    protected RoomPriceRepositoryInterface $roomPriceRepository;
    protected UsersRepositoryInterface $usersRepository;
    protected PricePackageRepositoryInterface $pricePackageRepository;
    protected BookingTimelineService $timelineService;
    protected ConflictChecker $conflictChecker;

    /**
     * Constructor
     *
     * @param BookingRepositoryInterface $bookingRepository
     */
    public function __construct(
        BookingRepositoryInterface $bookingRepository,
        RoomsRepositoryInterface $roomsRepository,
        BuildingsRepositoryInterface $buildingsRepository,
        RoomPriceRepositoryInterface $roomPriceRepository,
        UsersRepositoryInterface $usersRepository,
        PricePackageRepositoryInterface $pricePackageRepository,
        BookingTimelineService $timelineService,
        ConflictChecker $conflictChecker,
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->roomsRepository = $roomsRepository;
        $this->buildingsRepository = $buildingsRepository;
        $this->roomPriceRepository = $roomPriceRepository;
        $this->usersRepository = $usersRepository;
        $this->pricePackageRepository = $pricePackageRepository;
        $this->timelineService = $timelineService;
        $this->conflictChecker = $conflictChecker;
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
            $this->bookingRepository->update($id, [
                'status'              => BookingStatus::CANCELLED->value,
                'cancelled_at'        => $now,
                'cancellation_reason' => $reason,
            ]);
            $bookingUpdate = $this->bookingRepository->find($id);

            $this->timelineService->recordCancelled($id, $reason, $fromStatus, null, [
                'cancelled_at' => $now->toIso8601String(),
                'role'         => $role,
            ]);

            DB::commit();

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
     * Resolve partner_id (building owner) + property_id (building id) cho
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

        $building = $room->building;
        if (! $building) {
            return ['partner_id' => null, 'property_id' => null];
        }

        return [
            'partner_id'  => (int) $building->user_id,
            'property_id' => (int) $building->id,
        ];
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
            BookingStatus::PENDING->value   => BookingTimelineService::STATUS_PENDING,
            BookingStatus::CONFIRMED->value => BookingTimelineService::STATUS_CONFIRMED,
            BookingStatus::CANCELLED->value => BookingTimelineService::STATUS_CANCELLED,
            BookingStatus::COMPLETED->value => BookingTimelineService::STATUS_COMPLETED,
            default                         => 'unknown',
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
            $this->bookingRepository->update($id, [
                'status'       => BookingStatus::CONFIRMED->value,
                'confirmed_at' => $now,
            ]);
            $updated = $this->bookingRepository->find($id);

            // Sinh hợp đồng (Generate Contract)
            $room = $this->roomsRepository->find($booking->room_id);
            if ($room && $room->building) {
                $propertyType = $room->building->propertyType;
                $propertySlug = $propertyType ? strtolower($propertyType->slug) : '';

                $isLongTerm = in_array($propertySlug, ['can-ho', 'apartment', 'can-ho-dich-vu']);

                $contractType = $isLongTerm ? 'LEASE_AGREEMENT' : 'TERMS_AND_CONDITIONS';
                $contractStatus = $isLongTerm ? 0 : 1; // 0: Pending signature, 1: Auto-signed
                $contractTitle = $isLongTerm ? 'Hợp đồng thuê phòng / Căn hộ' : 'Phiếu xác nhận lưu trú';

                $content = "Hợp đồng cho mã đặt phòng " . sprintf('RM-%04d-%06d', date('Y'), $booking->id);

                $booking->contracts()->create([
                    'title'         => $contractTitle,
                    'content'       => $content,
                    'status'        => $contractStatus,
                    'type'          => 'Rental',
                    'contract_type' => $contractType,
                    'created_by'    => Auth::id() ?? 1,
                    'signature_date'=> $contractStatus === 1 ? $now : null,
                ]);
            }

            $this->timelineService->recordConfirmed($id, null, [
                'confirmed_at'  => $now->toIso8601String(),
                'contract_type' => $contractType ?? null,
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

            // get price to calculate total amount
            $price = $this->pricePackageRepository->getDefaultPriceOfRoom($roomId);

            // create booking
            $booking = $this->bookingRepository->create([
                'user_id'    => $createUser->id,
                'room_id'    => $roomId,
                'start_date' => $request->input('start_date'),
                'end_date'   => $request->input('end_date'),
                'price_id'   => $price->price_id ?? null,
                'note'       => $request->input('note'),
                'status'     => BookingStatus::PENDING->value,
                'created_by' => $createUser->id,
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


            // prepare room to send mail
            $room = $this->roomsRepository->getRoomInfoSendMail($roomId);

            // calculate total amount
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            $totalDays = $startDate->diffInDays($endDate) + 1;
            $totalAmount = ((float) ($price->cheapest_daily_price ?? 0)) * $totalDays;

            // Format services for email template (from selected services in booking)
            $selectedServices = $booking->services()->select('name', 'price')->get();
            $services = $selectedServices->map(fn($service) => [
                'name'   => $service->name,
                'amount' => (float) ($service->price ?? 0),
            ])->toArray();

            $emailInfo = [
                'booking_code'      => sprintf('RM-%04d-%06d', date('Y'), $booking->id),
                'room_title'        => $room->title,
                'room_description'  => $room->description,
                'room_deposit'      => $room->deposit ?? 0,
                'amenities'         => $room->amenities ?? [],
                'services'          => $services,
                'room_url'          => config('app.url_frontend') . '/rooms/' . $roomId,
                'bookings_url'      => config('app.url_frontend') . '/bks-stay/bookings/' . $booking->id,
                'is_first_time'     => $user ? false : true,
                'company_name'      => $room->company_name ?? '',
                'company_phone'     => $room->company_phone ?? '',
                'partner_address'   => $room->address ?? '',
                'building_name'     => $room->building_name ?? '',
                'building_address'  => $room->building_address ?? '',
                'start_time'        => $startDate->format('d/m/Y'),
                'end_time'          => $endDate->format('d/m/Y'),
                'total_days'        => $totalDays,
                'estimate_deadline' => Carbon::now()->addDays(7)->format('d/m/Y'),
                'total_amount'      => $totalAmount,
                'goline_phone'      => '0243 795 7250',
                'token'             => $token,
            ];
            DB::commit();

            // Send mail AFTER commit success
            SendBooking::dispatch($createUser->email, $createUser->name, $emailInfo);

            return [
                'success' => true,
                'data'    => null,
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

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

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

            DB::beginTransaction();
            $booking->update([
                'stay_status' => 'checked_out',
                'status' => BookingStatus::COMPLETED->value,
            ]);
            $this->roomsRepository->update($booking->room_id, ['status' => true]);
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
     * `checkUser` validate điều này thông qua join `buildings`.
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
}

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
use App\Jobs\SendBooking;
use App\Jobs\VerifyMail;
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
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->roomsRepository = $roomsRepository;
        $this->buildingsRepository = $buildingsRepository;
        $this->roomPriceRepository = $roomPriceRepository;
        $this->usersRepository = $usersRepository;
        $this->pricePackageRepository = $pricePackageRepository;
    }

    /**
     * Get all bookings or search bookings
     *
     * @param Request $request
     * @return array{success: bool, data: Booking|null, message: string}
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
     * Summary of handleCancelBooking
     * @param int $id
     * @return array{success: bool, data:null, message: array}
     */
    public function handleCancelBooking(Request $request, int $id): array
    {
        try {
            $booking = $this->bookingRepository->find($id);

            // check user authorization
            $checkUser = $this->bookingRepository->checkUser($request);
            if (!$checkUser) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.unauthorized'),
                ];
            }
            // If already cancelled, return message
            if ($booking->status === 'cancelled') {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('booking.messages.already_cancelled'),
                ];
            }
            // If booking is confirmed, set booking to cancelled and room to available
            if ($booking->status === 'confirmed') {
                DB::beginTransaction();
                $bookingUpdate = $this->bookingRepository->update($id, ['status' => 'cancelled']);
                $this->roomsRepository->update($booking->room_id, ['status' => 'available']);
                DB::commit();
                return [
                    'success' => true,
                    'data'    => $bookingUpdate,
                    'message' => __('booking.messages.cancelled_successfully'),
                ];
            } else {
                // If not confirmed, only set booking to cancelled
                $bookingUpdate = $this->bookingRepository->update($id, ['status' => 'cancelled']);
                return [
                    'success' => true,
                    'data'    => $bookingUpdate,
                    'message' => __('booking.messages.cancelled_successfully'),
                ];
            }
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
            if ($booking->status === 'confirmed') {
                // Free the room when deleting a confirmed booking
                $this->roomsRepository->update($booking->room_id, ['status' => 'available']);
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
                'room_url'          => config('app.url_frontend') . '/rooms/detail/' . $roomId,
                'bookings_url'      => config('app.url_frontend') . '/booking/' . $booking->id,
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
}

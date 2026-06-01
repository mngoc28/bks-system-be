<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\ContractRepository\ContractRepositoryInterface;
use App\Repositories\ServiceRepository\ServiceRepositoryInterface;
use App\Repositories\UsersRepository\UsersRepositoryInterface;
use Illuminate\Support\Collection;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\Contract;
use App\Services\DepositService;

final class StayService
{
    /**
     * @var BookingRepositoryInterface
     */
    protected $bookingRepository;

    /**
     * @var ContractRepositoryInterface
     */
    protected $contractRepository;

    /**
     * @var ServiceRepositoryInterface
     */
    protected $serviceRepository;

    /**
     * @var UsersRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var DepositService
     */
    protected $depositService;

    /**
     * Constructor
     */
    public function __construct(
        BookingRepositoryInterface $bookingRepository,
        ContractRepositoryInterface $contractRepository,
        ServiceRepositoryInterface $serviceRepository,
        UsersRepositoryInterface $userRepository,
        DepositService $depositService
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->contractRepository = $contractRepository;
        $this->serviceRepository = $serviceRepository;
        $this->userRepository = $userRepository;
        $this->depositService = $depositService;
    }

    /**
     * Get Dashboard Data for user
     *
     * @param int $userId
     * @return array
     */
    public function getDashboardData(int $userId): array
    {
        try {
            $user = $this->userRepository->find($userId);

            // Get total stays
            $totalStays = $this->bookingRepository->countStaysByUserId($userId);

            // Get accumulated spending
            $totalSpending = $this->bookingRepository->getTotalSpendingByUserId($userId);

            // Get active/upcoming booking
            $activeBooking = $this->bookingRepository->getActiveBookingByUserId($userId);

            // Recent history
            $recentHistory = $this->bookingRepository->getRecentHistoryByUserId($userId);

            return [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'reward_points' => $user->reward_points ?? 0,
                    'membership_level' => $user->membership_level ?? 'Bronze',
                ],
                'stats' => [
                    'total_stays' => $totalStays,
                    'accumulated_spending' => $totalSpending,
                ],
                'active_booking' => $activeBooking ? [
                    'id' => $activeBooking->id,
                    'room_title' => $activeBooking->room->title,
                    'start_date' => $activeBooking->start_date,
                    'end_date' => $activeBooking->end_date,
                    'status' => $activeBooking->status === 0 ? 'Upcoming' : 'In Progress',
                    'image' => $activeBooking->room->images[0]->image_url
                        ?? 'https://images.unsplash.com/photo-1590490359683-' .
                        '658d3d23f972?auto=format&fit=crop&q=80&w=800',
                    'location' => $activeBooking->room->property->name ?? 'N/A',
                ] : null,
                'recent_history' => $recentHistory->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'hotel' => $item->room->property->name ?? 'BKS Hotel',
                        'date' => $item->start_date,
                        'amount' => $item->total_amount,
                        'status' => 'Completed',
                    ];
                }),
                'has_pending_contract' => $activeBooking
                    ? ($activeBooking->contracts()->where('status', 0)->exists())
                    : false,
            ];
        } catch (Exception $e) {
            Log::error('Error in StayService@getDashboardData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get booking history
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBookingHistory(int $userId, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->bookingRepository->getBookingHistoryByUserId($userId, $perPage);
    }

    /**
     * Get booking detail
     *
     * @param int $id
     * @param int $userId
     * @return mixed
     */
    public function getBookingDetail(int $id, int $userId)
    {
        $booking = $this->bookingRepository->getBookingDetailByUserId($id, $userId);

        // Ensure a contract exists for pending bookings to make the flow work (only for long-term lease bookings)
        if ($booking && $booking->status === 0 && $booking->contracts->isEmpty()) {
            $room = $booking->room;
            $isLongTerm = false;
            if ($room && $room->property) {
                $propertyType = $room->property->propertyType;
                $propertySlug = $propertyType ? strtolower($propertyType->slug) : '';
                $isLongTerm = in_array($propertySlug, ['can-ho', 'apartment', 'can-ho-dich-vu']);
            }

            if ($isLongTerm) {
                Contract::create([
                    'booking_id' => $booking->id,
                    'title'      => 'Hợp đồng thuê phòng #' . $booking->id,
                    'content'    => 'Nội dung hợp đồng đang được cập nhật...',
                    'status'     => 0, // Pending
                    'contract_type' => 'LEASE_AGREEMENT',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
                $booking->load('contracts');
            }
        }

        return $booking;
    }

    /**
     * Get contracts
     *
     * @param int $userId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getContracts(int $userId): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Contract::whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['booking.room.property'])->orderBy('created_at', 'desc')->paginate(10);
    }

    /**
     * Get contract detail
     *
     * @param int $id
     * @param int $userId
     * @return mixed
     */
    public function getContractDetail(int $id, int $userId)
    {
        return $this->contractRepository->getContractDetail($id, $userId);
    }

    /**
     * Order a service for a booking
     *
     * @param int $userId
     * @param int $bookingId
     * @param int $serviceId
     * @return array
     */
    public function orderService(int $userId, int $bookingId, int $serviceId, ?string $note = null): array
    {
        try {
            $booking = $this->bookingRepository->find($bookingId);

            if (!$booking || $booking->user_id !== $userId) {
                return ['success' => false, 'message' => 'Booking not found or unauthorized'];
            }

            $service = $this->serviceRepository->find($serviceId);
            if (!$service) {
                return ['success' => false, 'message' => 'Service not found'];
            }

            DB::beginTransaction();
            $booking->services()->attach($serviceId, [
                'status'     => 0, // Pending
                'note'       => $note,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::commit();

            return ['success' => true, 'message' => 'Service ordered successfully'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in StayService@orderService: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Internal server error'];
        }
    }

    /**
     * Extend a booking
     *
     * @param int $bookingId
     * @param int $userId
     * @param string $newEndDate
     * @return array
     */
    public function extendBooking(int $bookingId, int $userId, string $newEndDate): array
    {
        try {
            $booking = Booking::find($bookingId);

            if (!$booking || $booking->user_id !== $userId) {
                return ['success' => false, 'message' => 'Booking not found or unauthorized'];
            }

            if ($booking->status !== 1 && $booking->status !== 2) {
                return ['success' => false, 'message' => 'Only active or confirmed bookings can be extended'];
            }

            $currentEndDate = \Carbon\Carbon::parse($booking->end_date);
            $newDate = \Carbon\Carbon::parse($newEndDate);

            if ($newDate->lte($currentEndDate)) {
                return ['success' => false, 'message' => 'New end date must be after current end date'];
            }

            DB::beginTransaction();
            $booking->update([
                'end_date' => $newEndDate,
                'updated_at' => now(),
            ]);
            DB::commit();

            return ['success' => true, 'message' => 'Stay extension request processed successfully'];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in StayService@extendBooking: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Internal server error'];
        }
    }

    /**
     * Get service requests for a partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getPartnerServiceRequests(int $partnerId): array
    {
        // Get all properties for this partner
        $propertyIds = \App\Models\Property::where('user_id', $partnerId)->pluck('id')->toArray();
        if (empty($propertyIds)) {
            return [];
        }

        // Get service requests (pivot records) for those properties
        return \App\Models\BookingService::with(['booking.room.property', 'booking.user', 'service'])
            ->whereHas('booking.room', function ($query) use ($propertyIds) {
                $query->whereIn('property_id', $propertyIds);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Update service request status
     *
     * @param int $requestId
     * @param int $partnerId
     * @param int $status
     * @return array
     */
    public function updateServiceRequestStatus(int $requestId, int $partnerId, int $status): array
    {
        $request = \App\Models\BookingService::with('booking.room.property')->find($requestId);
        if (!$request) {
            return ['success' => false, 'message' => 'Request not found'];
        }

        // Check if partner owns the property
        if ($request->booking->room->property->user_id !== $partnerId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $request->update([
            'status' => $status,
            'updated_by' => $partnerId,
            'updated_at' => now(),
        ]);

        return ['success' => true, 'message' => 'Service request status updated successfully'];
    }

    /**
     * Submit deposit receipt for a booking
     *
     * @param int $bookingId
     * @param int $userId
     * @param string $receiptPath
     * @return array
     */
    public function submitDepositReceipt(int $bookingId, int $userId, string $receiptPath): array
    {
        try {
            $booking = $this->bookingRepository->find($bookingId);
            if (!$booking || $booking->user_id !== $userId) {
                return ['success' => false, 'message' => 'Booking not found or unauthorized'];
            }

            $success = $this->depositService->submitReceipt($bookingId, $receiptPath);
            if (!$success) {
                return ['success' => false, 'message' => 'Failed to submit receipt'];
            }

            return ['success' => true, 'message' => 'Receipt submitted successfully'];
        } catch (Exception $e) {
            Log::error('Error in StayService@submitDepositReceipt: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Internal server error'];
        }
    }
}

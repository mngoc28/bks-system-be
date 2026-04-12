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
     * Constructor
     */
    public function __construct(
        BookingRepositoryInterface $bookingRepository,
        ContractRepositoryInterface $contractRepository,
        ServiceRepositoryInterface $serviceRepository,
        UsersRepositoryInterface $userRepository
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->contractRepository = $contractRepository;
        $this->serviceRepository = $serviceRepository;
        $this->userRepository = $userRepository;
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
                    'location' => $activeBooking->room->building->name ?? 'N/A',
                ] : null,
                'recent_history' => $recentHistory->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'hotel' => $item->room->building->name ?? 'BKS Hotel',
                        'date' => $item->start_date,
                        'amount' => $item->price->price ?? 0,
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
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBookingHistory(int $userId): Collection
    {
        return $this->bookingRepository->getBookingHistoryByUserId($userId);
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
        })->with(['booking.room.building'])->orderBy('created_at', 'desc')->paginate(10);
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
    public function orderService(int $userId, int $bookingId, int $serviceId): array
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
}

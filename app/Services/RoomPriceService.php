<?php

namespace App\Services;

use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\RoomPriceRepository\RoomPriceRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class RoomPriceService
{
    protected $roomPriceRepository;
    protected $bookingRepository;

    /**
     * Constructor method.
     *
     * @param RoomPriceRepositoryInterface $roomPriceRepository
     */
    public function __construct(
        RoomPriceRepositoryInterface $roomPriceRepository,
        BookingRepositoryInterface $bookingRepository
    ) {
        $this->roomPriceRepository = $roomPriceRepository;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Save room prices
     *
     * @param int $roomId
     * @param array $prices
     */
    public function saveRoomPrices($roomId, $prices)
    {
        try {
            if (!empty($prices) && is_array($prices) && $roomId) {
                $newPriceKeys = [];
                foreach ($prices as $price) {
                    $this->roomPriceRepository->updateOrCreate(
                        [
                            'room_id' => $roomId,
                            'price_package_id' => $price['price_package_id'],
                            'unit' => $price['unit'],
                        ],
                        [
                            'price' => $price['unit_price'],
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]
                    );
                    $newPriceKeys[] = ['price_package_id' => $price['price_package_id'], 'unit' => $price['unit']];
                }

                // Delete old prices not in new list, if not referenced in bookings
                $existingPrices = $this->roomPriceRepository->findBy(['room_id' => $roomId]);
                $newKeys = array_map(fn($k) => $k['price_package_id'] . '-' . $k['unit'], $newPriceKeys);
                foreach ($existingPrices as $existingPrice) {
                    $key = $existingPrice['price_package_id'] . '-' . $existingPrice['unit'];
                    if (!in_array($key, $newKeys)) {
                        // Check if referenced in bookings
                        $bookingCount = $this->bookingRepository->countRecord(
                            ['price_id' => $existingPrice['id']]
                        );
                        if ($bookingCount > 0) {
                            return [
                                'success' => false,
                                'message' => __('room.messages.price_in_use', [
                                    'price_package_id' => $existingPrice['price_package_id'],
                                    'unit' => $existingPrice['unit']
                                ])
                            ];
                        }
                        $this->roomPriceRepository->delete($existingPrice['id']);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error(__('room.messages.save_prices_failed'), [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
            ]);
            throw $e;
        }
        return ['success' => true];
    }
}

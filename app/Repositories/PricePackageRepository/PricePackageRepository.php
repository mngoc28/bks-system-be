<?php

namespace App\Repositories\PricePackageRepository;

use App\Models\PricePackage;
use App\Repositories\BaseRepository;

class PricePackageRepository extends BaseRepository implements PricePackageRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return PricePackage::class;
    }

    /**
     * Get all price packages without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPricePackages(): \Illuminate\Support\Collection
    {
        return $this->model->select('id', 'name')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get price packages by room ID
     *
     * @param int $roomId
     * @return \Illuminate\Support\Collection
     */
    public function getPricePackagesByRoomId(int $roomId): \Illuminate\Support\Collection
    {
        return $this->model
            ->join('room_prices', 'price_packages.id', '=', 'room_prices.price_package_id')
            ->where('room_prices.room_id', $roomId)
            ->select(
                'room_prices.id as room_price_id',
                'price_packages.name',
                'room_prices.price',
                'room_prices.unit'
            )
            ->get();
    }

    /**
     * Get default price package info of a room by room ID
     *
     * @param int $roomId
     * @return object|null
     */
    public function getDefaultPriceOfRoom(int $roomId): object|null
    {
        return $this->model
            ->join('room_prices as rp', 'price_packages.id', '=', 'rp.price_package_id')
            ->where('rp.room_id', $roomId)
            ->selectRaw(
                'price_packages.id as package_id, rp.id as price_id, ROUND(CASE
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                        AND MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) IS NOT NULL
                    THEN LEAST(
                        MIN(CASE WHEN rp.unit = "day" THEN rp.price END),
                        MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                    )
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                    THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                    ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                END, 0) as cheapest_daily_price'
            )
            ->groupBy('rp.room_id', 'price_packages.id', 'rp.id')
            ->first();
    }
}

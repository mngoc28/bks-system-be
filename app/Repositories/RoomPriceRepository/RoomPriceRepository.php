<?php

namespace App\Repositories\RoomPriceRepository;

use App\Models\RoomPrice;
use App\Repositories\BaseRepository;

class RoomPriceRepository extends BaseRepository implements RoomPriceRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return RoomPrice::class;
    }
}

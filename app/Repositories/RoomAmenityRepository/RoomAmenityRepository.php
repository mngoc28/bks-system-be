<?php

namespace App\Repositories\RoomAmenityRepository;

use App\Models\RoomAmenity;
use App\Repositories\BaseRepository;

class RoomAmenityRepository extends BaseRepository implements RoomAmenityRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return RoomAmenity::class;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories\RoomAmenityRepository;

use App\Models\RoomAmenity;
use App\Repositories\BaseRepository;

/**
 * Class RoomAmenityRepository
 *
 * @package App\Repositories\RoomAmenityRepository
 */
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

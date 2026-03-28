<?php

namespace App\Repositories\RoomServiceRepository;

use App\Models\RoomService;
use App\Repositories\BaseRepository;
use App\Repositories\RoomServiceRepository\RoomServiceRepositoryInterface;

class RoomServiceRepository extends BaseRepository implements RoomServiceRepositoryInterface
{
    /**
     * Get the model for the repository
     *
     * @return RoomService|mixed
     */
    public function getModel()
    {
        return RoomService::class;
    }
}

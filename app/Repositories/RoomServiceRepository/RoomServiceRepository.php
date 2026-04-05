<?php

declare(strict_types=1);

namespace App\Repositories\RoomServiceRepository;

use App\Models\RoomService;
use App\Repositories\BaseRepository;
use App\Repositories\RoomServiceRepository\RoomServiceRepositoryInterface;

/**
 * Class RoomServiceRepository
 *
 * @package App\Repositories\RoomServiceRepository
 */
class RoomServiceRepository extends BaseRepository implements RoomServiceRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return RoomService::class;
    }
}

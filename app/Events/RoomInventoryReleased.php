<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomInventoryReleased
{
    use Dispatchable;
    use SerializesModels;

    /**
     * The ID of the room that has been freed.
     *
     * @var int
     */
    public int $roomId;

    /**
     * Create a new event instance.
     *
     * @param int $roomId
     */
    public function __construct(int $roomId)
    {
        $this->roomId = $roomId;
    }
}

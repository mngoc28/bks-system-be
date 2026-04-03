<?php

declare(strict_types=1);

namespace App\Repositories\RoomsRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

interface RoomsRepositoryInterface extends RepositoryInterface
{
    /**
     * get empty list in room
     * @return int
     */
    public function getEmptyRooms(): int;

    /**
     * Get all rooms or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return LengthAwarePaginator
     */
    public function getAllOrSearchRooms($request): LengthAwarePaginator;

    /**
     * Get room details by ID
     *
     * @param int $id
     * @return mixed
     */
    public function roomDetail($id): mixed;

    /**
     * Get room names by building ID
     *
     * @param int $buildingId
     * @return Collection
     */
    public function getRoomNamesByBuildingId(int $buildingId): Collection;

    /**
     * Get latest rooms
     *
     * @param Request $request
     * @return array
     */
    public function getLatestRooms(Request $request): object | null;

    /**
     * Get room list with filters
     *
     * @param \Illuminate\Http\Request $request
     * @return object|null
     */
    public function getRoomList(Request $request): object | null;

    /**
     * Get room info for sending booking email
     *
     * @param int $roomId
     * @return object|null
     */
    public function getRoomInfoSendMail(int $roomId): object | null;

    /**
     * Get public room detail by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getPublicRoomDetail(int $id): object | null;

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get all rooms for a specific partner
     *
     * @param int $partnerId
     * @param mixed $request
     * @return LengthAwarePaginator
     */
    public function getRoomsForPartner(int $partnerId, $request): LengthAwarePaginator;

    /**
     * Get room detail for a specific partner
     *
     * @param int $id
     * @param int $partnerId
     * @return mixed
     */
    public function getRoomDetailForPartner(int $id, int $partnerId): mixed;

    /**
     * Count rooms for a specific partner
     *
     * @param int $partnerId
     * @param array $filters
     * @return int
     */
    public function countRoomsForPartner(int $partnerId, array $filters = []): int;

    /**
     * Get empty rooms for a specific partner
     *
     * @param int $partnerId
     * @return int
     */
    public function getEmptyRoomsForPartner(int $partnerId): int;
}

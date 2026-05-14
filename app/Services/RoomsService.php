<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Repositories\RoomServiceRepository\RoomServiceRepository;
use App\Repositories\RoomAmenityRepository\RoomAmenityRepository;
use App\Repositories\RoomPriceRepository\RoomPriceRepository;
use App\Models\Property;
use App\Models\Room;
use App\Models\PricePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

final class RoomsService
{
    /**
     * Repository layer that handles data operations for rooms.
     */
    protected RoomsRepositoryInterface $roomsRepository;
    protected RoomServiceRepository $roomServiceRepository;
    protected RoomAmenityRepository $roomAmenityRepository;
    protected RoomPriceRepository $roomPriceRepository;
    private RoomServiceService $roomServiceService;
    private RoomAmenityService $roomAmenityService;
    private RoomPriceService $roomPriceService;
    private UtilityFeeService $utilityFeeService;

    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependency (RoomsRepositoryInterface)
     * using Dependency Injection.
     *
     * @param RoomsRepositoryInterface $roomsRepository Handles data operations for rooms
     * @param RoomServiceService $roomServiceService Handles business logic for room services
     * @param RoomAmenityService $roomAmenityService Handles business logic for room amenities
     * @param RoomPriceService $roomPriceService Handles business logic for room prices
     */

    public function __construct(
        RoomsRepositoryInterface $roomsRepository,
        RoomServiceRepository $roomServiceRepository,
        RoomAmenityRepository $roomAmenityRepository,
        RoomPriceRepository $roomPriceRepository,
        RoomServiceService $roomServiceService,
        RoomAmenityService $roomAmenityService,
        RoomPriceService $roomPriceService,
        UtilityFeeService $utilityFeeService
    ) {
        $this->roomsRepository = $roomsRepository;
        $this->roomServiceRepository = $roomServiceRepository;
        $this->roomAmenityRepository = $roomAmenityRepository;
        $this->roomPriceRepository = $roomPriceRepository;
        $this->roomServiceService = $roomServiceService;
        $this->roomAmenityService = $roomAmenityService;
        $this->roomPriceService = $roomPriceService;
        $this->utilityFeeService = $utilityFeeService;
    }
    /**
     * Get all rooms or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getAllOrSearchRooms($request): array
    {
        try {
            $rooms = $this->roomsRepository->getAllOrSearchRooms($request);

            return [
                "success" => true,
                "data" => $rooms,
                "message" => __("room.messages.retrieved_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching rooms: " . $e->getMessage());

            return [
                "success" => false,
                "message" => __("room.messages.retrieved_failed"),
            ];
        }
    }

    /**
     * Get a room by its ID
     *
     * @param int $id
     * @return array{success: bool, data: Room|null, message: string}
     */
    public function getRoomById($id): array
    {
        try {
            $room = $this->roomsRepository->roomDetail($id);

            return [
                "success" => true,
                "data" => $room,
                "message" => __("room.messages.found_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching room by ID: " . $e->getMessage());

            return [
                "success" => false,
                "data" => null,
                "message" => __("room.messages.not_found"),
            ];
        }
    }

    /**
     * Create a new room
     *
     * @param Request $request
     * @return array{success: bool, data: Room|null, message: string}
     */
    public function createRoom($request): array
    {
        try {
            DB::beginTransaction();

            // 1. Create room with basic info
            $roomData = $request->except(["amenities", "services", "prices"]);

            $roomData["created_by"] = Auth::id();
            $roomData["updated_by"] = Auth::id();
            $room = $this->roomsRepository->create($roomData);

            // 2. Save room amenities
            if (
                $request->filled("amenities") &&
                is_array($request->amenities)
            ) {
                $this->roomAmenityService->saveRoomAmenities(
                    $room->id,
                    $request->amenities
                );
            }

            // 3. Save room services
            if ($request->filled("services") && is_array($request->services)) {
                $this->roomServiceService->saveServiceCheckbox(
                    $room->id,
                    $request->services
                );
            }

            // 4. Save room prices
            if ($request->filled("prices") && is_array($request->prices)) {
                $priceResult = $this->roomPriceService->saveRoomPrices(
                    $room->id,
                    $request->prices
                );
                if (is_array($priceResult) && isset($priceResult['success']) && !$priceResult['success']) {
                    DB::rollBack();
                    return [
                        "success" => false,
                        "data" => null,
                        "message" => $priceResult['message'],
                    ];
                }
            }

            // 5. Save utility fees
            if ($request->filled("utility_fees") && is_array($request->utility_fees)) {
                $this->utilityFeeService->saveUtilityFees(
                    $room->id,
                    $request->utility_fees
                );
            }

            DB::commit();

            // Load relationships for response
            $room->load(["amenities", "services", "prices"]);

            return [
                "success" => true,
                "data" => $room,
                "message" => __("room.messages.created_successfully"),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error storing room: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("room.messages.create_failed"),
            ];
        }
    }

    /**
     * Update an existing room
     *
     * @param Request $request
     * @param int $id
     * @return array{success: bool, data: Room|null, message: string}
     */
    public function updateRoom($request, $id): array
    {
        DB::beginTransaction();
        try {
            // not update amenities, services, prices into rooms table
            $roomData = $request->except(["amenities", "services", "prices"]);
            $roomData["updated_by"] = Auth::id();
            $room = $this->roomsRepository->update($id, $roomData);

            // Handle relationships separately
            $this->roomServiceRepository->deleteBy(["room_id" => $id]);
            $this->roomServiceService->saveServiceCheckbox(
                $id,
                $request->services ?? []
            );
            $this->roomAmenityRepository->deleteBy(["room_id" => $id]);
            $this->roomAmenityService->saveRoomAmenities(
                $id,
                $request->amenities ?? []
            );
            $priceResult = $this->roomPriceService->saveRoomPrices(
                $id,
                $request->prices ?? []
            );
            if (is_array($priceResult) && isset($priceResult['success']) && !$priceResult['success']) {
                DB::rollBack();
                return [
                    "success" => false,
                    "data" => null,
                    "message" => $priceResult['message'],
                ];
            }

            // Update utility fees
            if ($request->filled("utility_fees") && is_array($request->utility_fees)) {
                $this->utilityFeeService->saveUtilityFees(
                    $id,
                    $request->utility_fees
                );
            }

            DB::commit();
            return [
                "success" => true,
                "data" => $room,
                "message" => __("room.messages.updated_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Error updating room: " . $e->getMessage());
            DB::rollBack();
            return [
                "success" => false,
                "data" => null,
                "message" => __("room.messages.update_failed"),
            ];
        }
    }

    /**
     * Summary of deleteRoom
     * @param int $id
     * @return array{data: null, message: array|string|null, success: bool}
     */
    public function deleteRoom($id): array
    {
        DB::beginTransaction();
        try {
            // Delete room
            $deleted = $this->roomsRepository->delete($id);
            DB::commit();
            if (!$deleted) {
                return [
                    "success" => false,
                    "message" => __("room.messages.not_found"),
                    "data" => null,
                ];
            }
            return [
                "success" => true,
                "message" => __("room.messages.deleted_successfully"),
                "data" => null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error deleting room: " . $e->getMessage(), [
                "trace" => $e->getTraceAsString(),
            ]);
            return [
                "success" => false,
                "data" => null,
                "message" => __("room.messages.delete_failed"),
            ];
        }
    }

    /**
     * Summary of getRoomNamesByPropertyId
     * @param int $propertyId
     * @return mixed
     */
    public function getRoomNamesByPropertyId($propertyId): mixed
    {
        try {
            $propertyId = (int) $propertyId;
            $roomNames = $this->roomsRepository->getRoomNamesByPropertyId($propertyId);
            return [
                "success" => true,
                "data" => $roomNames,
                "message" => __("room.messages.retrieved_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching room names by property ID: " . $e->getMessage());
            return [
                "success" => false,
                "message" => __("room.messages.retrieved_failed"),
            ];
        }
    }

    // ====== The functions below are APIs for the end user ======
    /**
     * Get latest rooms
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getLatestRooms(Request $request): array
    {
        try {
            $rooms = $this->roomsRepository->getLatestRooms($request);

            return [
                "success" => true,
                "data" => $rooms,
                "message" => __("room.messages.retrieved_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching rooms: " . $e->getMessage());

            return [
                "success" => false,
                "message" => __("room.messages.retrieved_failed"),
            ];
        }
    }

    /**
     * Get room list with filters
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function handleRoomList($request): array
    {
        try {
            $rooms = $this->roomsRepository->getRoomList($request);

            return [
                "success" => true,
                "data" => $rooms,
                "message" => __("room.messages.retrieved_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching rooms: " . $e->getMessage());

            return [
                "success" => false,
                "message" => __("room.messages.retrieved_failed"),
            ];
        }
    }

    /**
     * Get public room detail by ID
     *
     * @param int $id
     * @return array{success: bool, data: Room|null, message: string}
     */
    public function handlePublicRoomDetail($id): array
    {
        try {
            $room = $this->roomsRepository->getPublicRoomDetail((int)$id);

            return [
                "success" => true,
                "data" => $room,
                "message" => __("room.messages.found_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching room by ID: " . $e->getMessage());

            return [
                "success" => false,
                "data" => null,
                "message" => __("room.messages.not_found"),
            ];
        }
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get all rooms for partner
     *
     * @param Request $request
     * @return array
     */
    public function handleGetAllRoomsForPartner(Request $request): array
    {
        try {
            $partnerId = Auth::id();
            $rooms = $this->roomsRepository->getRoomsForPartner($partnerId, $request);

            return [
                "success" => true,
                "data" => $rooms,
                "message" => __("room.messages.retrieved_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Partner get rooms failed: " . $e->getMessage());
            return [
                "success" => false,
                "message" => __("room.messages.retrieved_failed"),
            ];
        }
    }

    /**
     * Get room detail for partner
     *
     * @param int $id
     * @return array
     */
    public function handleGetRoomDetailForPartner(int $id): array
    {
        try {
            $partnerId = Auth::id();
            $room = $this->roomsRepository->getRoomDetailForPartner((int)$id, $partnerId);
            if (!$room) {
                return [
                    "success" => false,
                    "message" => __("room.messages.not_found"),
                ];
            }
            return [
                "success" => true,
                "data" => $room,
                "message" => __("room.messages.found_successfully"),
            ];
        } catch (\Exception $e) {
            Log::error("Partner get room detail failed: " . $e->getMessage());
            return [
                "success" => false,
                "message" => __("room.messages.not_found"),
            ];
        }
    }

    /**
     * Get all price packages for partners
     */
    public function handleGetPricePackages(): array
    {
        try {
            $packages = \App\Models\PricePackage::where('status', true)->get();
            return [
                "success" => true,
                "data" => $packages,
                "message" => "Lấy danh sách gói giá thành công.",
            ];
        } catch (\Exception $e) {
            return ["success" => false, "message" => "Lỗi: " . $e->getMessage(), "data" => []];
        }
    }

    /**
     * Get rooms occupancy statistics for partner
     */
    public function handleGetRoomsOccupancy(Request $request): array
    {
        try {
            $partnerId  = Auth::id();
            $propertyId = (int) $request->input('property_id');

            if (! $propertyId) {
                return ['success' => false, 'message' => 'Vui lòng chọn bất động sản.', 'data' => null];
            }

            $occupancyData = $this->roomsRepository->getOccupancyForPartner($partnerId, $propertyId);

            // Calculate stats
            $total = $occupancyData->count();
            $vacant = $occupancyData->where('occupancy_status', 'vacant')->count();
            $occupied = $occupancyData->where('occupancy_status', 'occupied')->count();
            $maintenance = $occupancyData->where('occupancy_status', 'maintenance')->count();
            $hidden = $occupancyData->where('occupancy_status', 'hidden')->count();

            return [
                "success" => true,
                "data" => [
                    "rooms" => $occupancyData,
                    "stats" => [
                        "total" => $total,
                        "vacant" => $vacant,
                        "occupied" => $occupied,
                        "maintenance" => $maintenance,
                        "hidden" => $hidden,
                        "percentage" => $total > 0 ? round(($occupied / $total) * 100, 1) : 0
                    ]
                ],
                "message" => "Lấy thông tin lấp đầy thành công.",
            ];
        } catch (\Exception $e) {
            Log::error("Get occupancy failed: " . $e->getMessage());
            return ["success" => false, "message" => $e->getMessage(), "data" => null];
        }
    }

    /**
     * Bulk create rooms
     */
    public function handleBulkCreateRooms(Request $request): array
    {
        try {
            DB::beginTransaction();
            $roomsData   = $request->input('rooms', []);
            $propertyId  = (int) $request->input('property_id');
            $partnerId   = (int) Auth::id();

            if (empty($roomsData) || ! is_array($roomsData)) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Danh sách phòng không hợp lệ.',
                ];
            }

            $propertyOwned = Property::query()
                ->where('id', $propertyId)
                ->where('user_id', $partnerId)
                ->exists();

            if (! $propertyOwned) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Bạn không có quyền thao tác với bất động sản này.',
                ];
            }

            $amenities = $request->input('amenities', []);
            $services = $request->input('services', []);
            $prices = $request->input('prices', []);

            $createdRooms = [];

            foreach ($roomsData as $data) {
                $roomName = trim((string) ($data['name'] ?? ''));
                if ($roomName === '') {
                    continue;
                }

                $room = $this->roomsRepository->create([
                    'property_id' => $propertyId,
                    'title'       => $roomName,
                    'room_number' => $roomName,
                    'area'        => $data['area'] ?? $request->input('area', 0),
                    'floor_number'=> (int) $request->input('floor_number', 1),
                    'people'      => (int) $request->input('people', 1),
                    'room_type'   => (int) $request->input('room_type', 1),
                    'status'      => (bool) $request->input('status', true),
                    'created_by'  => Auth::id(),
                    'updated_by'  => Auth::id(),
                ]);

                if (is_array($amenities) && !empty($amenities)) {
                    $this->roomAmenityService->saveRoomAmenities($room->id, $amenities);
                }

                if (is_array($services) && !empty($services)) {
                    $this->roomServiceService->saveServiceCheckbox($room->id, $services);
                }

                if (is_array($prices) && !empty($prices)) {
                    $priceResult = $this->roomPriceService->saveRoomPrices($room->id, $prices);
                    if (is_array($priceResult) && isset($priceResult['success']) && !$priceResult['success']) {
                        DB::rollBack();
                        return [
                            "success" => false,
                            "message" => $priceResult['message'],
                        ];
                    }
                }

                if (is_array($request->utility_fees) && !empty($request->utility_fees)) {
                    $this->utilityFeeService->saveUtilityFees($room->id, $request->utility_fees);
                }

                $createdRooms[] = $room;
            }

            if (count($createdRooms) === 0) {
                DB::rollBack();
                return [
                    "success" => false,
                    "message" => "Không có phòng hợp lệ để tạo.",
                ];
            }

            DB::commit();
            return [
                "success" => true,
                "data" => $createdRooms,
                "message" => "Tạo hàng loạt " . count($createdRooms) . " phòng thành công.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ["success" => false, "message" => "Lỗi tạo hàng loạt: " . $e->getMessage()];
        }
    }

    /**
     * Bulk update status
     */
    public function handleBulkUpdateStatus(array $ids, $status): array
    {
        try {
            $partnerId = (int) Auth::id();
            Room::query()
                ->whereIn('id', $ids)
                ->whereHas('property', function ($query) use ($partnerId) {
                    $query->where('user_id', $partnerId);
                })
                ->update(['status' => $status, 'updated_by' => $partnerId]);

            return [
                "success" => true,
                "data" => null,
                "message" => "Cập nhật trạng thái thành công.",
            ];
        } catch (\Exception $e) {
            return ["success" => false, "message" => "Lỗi cập nhật: " . $e->getMessage()];
        }
    }

    /**
     * Bulk delete rooms
     */
    public function handleBulkDeleteRooms(array $ids): array
    {
        try {
            $partnerId = (int) Auth::id();
            Room::query()
                ->whereIn('id', $ids)
                ->whereHas('property', function ($query) use ($partnerId) {
                    $query->where('user_id', $partnerId);
                })
                ->delete();

            return [
                "success" => true,
                "data" => null,
                "message" => "Xóa thành công " . count($ids) . " phòng.",
            ];
        } catch (\Exception $e) {
            return ["success" => false, "message" => "Lỗi xóa hàng loạt: " . $e->getMessage()];
        }
    }
}

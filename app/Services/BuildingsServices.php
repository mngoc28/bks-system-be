<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Building;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\BuildingRepository\BuildingsRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class BuildingsServices
{
    /**
     * Building repository instance
     */
    protected BuildingsRepositoryInterface $buildingRepository;

    /**
     * Booking repository instance
     */
    protected BookingRepositoryInterface $bookingRepository;

    /**
     * Constructor
     *
     * @param BuildingsRepositoryInterface $buildingRepository
     * @param BookingRepositoryInterface $bookingRepository
     */
    public function __construct(
        BuildingsRepositoryInterface $buildingRepository,
        BookingRepositoryInterface $bookingRepository
    ) {
        $this->buildingRepository = $buildingRepository;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Get all buildings or search buildings
     *
     * @param Request $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllOrSearchBuildings(Request $request): array
    {
        try {
            $buildings = $this->buildingRepository->getAllOrSearchBuildings($request, (array)$request->sort);
            if (!$buildings) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("building.messages.retrieved_failed"),
                ];
            }
            return [
                "success" => true,
                "data" => $buildings,
                "message" => __("building.messages.retrieved_successfully"),
            ];
        } catch (Exception $e) {
            Log::error(__("building.messages.retrieved_failed"), [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("building.messages.retrieved_failed"),
            ];
        }
    }

    /**
     * Get all buildings without pagination
     *
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllBuildingNames(): array
    {
        try {
            $buildings = $this->buildingRepository->getAllBuildingNames();

            return [
                "success" => true,
                "data" => $buildings,
                "message" => __("building.messages.retrieved_successfully"),
            ];
        } catch (Exception $e) {
            Log::error(__("building.messages.retrieved_failed"), [
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("building.messages.retrieved_failed"),
            ];
        }
    }

    /**
     * Get building by ID
     *
     * @param int $id
     * @return array
     */
    public function getBuildingById(int $id): array
    {
        try {
            $building = $this->buildingRepository->getBuildingById($id);
            if (!$building) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("building.messages.not_found"),
                ];
            }
            return [
                "success" => true,
                "data" => $building,
                "message" => __("building.messages.found_successfully"),
            ];
        } catch (Exception $e) {
            Log::error(__("building.messages.find_failed"), [
                "building_id" => $id,
                "error" => $e->getMessage(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("building.messages.find_failed"),
            ];
        }
    }

    /**
     * Create new building
     *
     * @param array $data
     * @return array{success: bool, data: Building|null, message: string}
     */
    public function createBuilding(array $data): array
    {
        try {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $created = $this->buildingRepository->create($data);
            if (!$created) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("building.messages.create_failed"),
                ];
            }
            return [
                "success" => true,
                "data" => $created,
                "message" => __("building.messages.created_successfully"),
            ];
        } catch (Exception $e) {
            Log::error(__("building.messages.create_failed"), [
                "data" => $data,
                "error" => $e->getMessage(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("building.messages.create_failed"),
            ];
        }
    }

    /**
     * Update building
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateBuilding(int $id, array $data): array
    {
        try {
            $updated = $this->buildingRepository->update($id, array_merge($data, [
                'updated_by' => Auth::id(),
            ]));

            if (!$updated) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("building.messages.update_failed"),
                ];
            }

            return [
                "success" => true,
                "data" => $updated,
                "message" => __("building.messages.updated_successfully"),
            ];
        } catch (Exception $e) {
            Log::error(__("building.messages.update_failed"), [
                "building_id" => $id,
                "data" => $data,
                "error" => $e->getMessage(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("building.messages.update_failed"),
            ];
        }
    }

    /**
     * Delete building
     *
     * @param int $id
     * @return array{success: bool, data: null, message: string}
     */
    public function deleteBuilding(int $id): array
    {
        try {
            $deleted = $this->buildingRepository->delete($id);

            if (!$deleted) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("building.messages.delete_failed"),
                ];
            }

            return [
                "success" => true,
                "data" => $deleted,
                "message" => __("building.messages.deleted_successfully"),
            ];
        } catch (Exception $e) {
            Log::error(__("building.messages.delete_failed"), [
                "building_id" => $id,
                "error" => $e->getMessage(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("building.messages.delete_failed"),
            ];
        }
    }

    /**
     * Get bookings by building ID
     *
     * @param int $buildingId
     * @param Request $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getBookingsByBuilding(int $buildingId, $request): array
    {
        try {
            $request->merge(["building_id" => $buildingId]);

            $bookings = $this->bookingRepository->getAllOrSearchBookings($request);

            if (!$bookings) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("building.messages.bookings_retrieved_failed"),
                ];
            }

            return [
                "success" => true,
                "data" =>  $bookings,
                "message" => __(
                    "building.messages.bookings_retrieved_successfully"
                ),
            ];
        } catch (Exception $e) {
            Log::error(__("building.messages.bookings_retrieved_failed"), [
                "building_id" => $buildingId,
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            return [
                "success" => false,
                "data" => null,
                "message" => __("building.messages.bookings_retrieved_failed"),
            ];
        }
    }

    /**
     * Get all buildings types
     *
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllBuildingsTypes(): array
    {
        try {
            $buildingsTypes = $this->buildingRepository->getAllBuildingsTypes();

            if (!$buildingsTypes) {
                return [
                    "success" => false,
                    "data" => null,
                    "message" => __("building.messages.buildings_types_retrieved_failed"),
                ];
            }
            return [
                "success" => true,
                "data" => $buildingsTypes,
                "message" => __(
                    "building.messages.buildings_types_retrieved_successfully"
                ),
            ];
        } catch (Exception $e) {
            Log::error(
                __("building.messages.buildings_types_retrieved_failed"),
                [
                    "error" => $e->getMessage(),
                    "trace" => $e->getTraceAsString(),
                ]
            );

            return [
                "success" => false,
                "data" => null,
                "message" => __(
                    "building.messages.buildings_types_retrieved_failed"
                ),
            ];
        }
    }
}

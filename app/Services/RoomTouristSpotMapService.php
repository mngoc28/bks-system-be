<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RoomTouristSpotMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class RoomTouristSpotMapService
{
    public function index(Request $request): array
    {
        try {
            $query = RoomTouristSpotMap::query()->with(['room:id,title', 'touristSpot:id,name,slug,is_featured']);

            if ($request->filled('room_id')) {
                $query->where('room_id', (int) $request->input('room_id'));
            }

            if ($request->filled('tourist_spot_id')) {
                $query->where('tourist_spot_id', (int) $request->input('tourist_spot_id'));
            }

            $maps = $query->orderByDesc('is_primary')
                ->orderBy('distance_km')
                ->orderBy('travel_time_minutes')
                ->orderByDesc('id')
                ->paginate((int) $request->input('per_page', config('const.DEFAULT_PER_PAGE')));

            return [
                'success' => true,
                'data' => $maps,
                'message' => 'Lấy danh sách mapping điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error listing room tourist spot maps: ' . $throwable->getMessage(), [
                'trace' => $throwable->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Lấy danh sách mapping điểm du lịch thất bại.',
            ];
        }
    }

    public function store(array $data): array
    {
        try {
            $applyToAllRooms = !empty($data['apply_to_all_rooms']);
            unset($data['apply_to_all_rooms']);

            $data['source_type'] = 'manual';

            $map = DB::transaction(function () use ($data, $applyToAllRooms): RoomTouristSpotMap {
                // If setting this mapping as primary, clear primary flag for all existing mappings of this room
                if (!empty($data['is_primary'])) {
                    RoomTouristSpotMap::query()
                        ->where('room_id', (int) $data['room_id'])
                        ->update(['is_primary' => false]);
                }

                $mainMap = RoomTouristSpotMap::query()->create($data);

                if ($applyToAllRooms) {
                    $roomId = (int) $data['room_id'];
                    $touristSpotId = (int) $data['tourist_spot_id'];

                    // Get property ID of the current room
                    $propertyId = DB::table('rooms')->where('id', $roomId)->value('property_id');
                    if ($propertyId) {
                        // Find all other rooms in the same property
                        $otherRoomIds = DB::table('rooms')
                            ->where('property_id', $propertyId)
                            ->where('id', '!=', $roomId)
                            ->pluck('id')
                            ->toArray();

                        if (!empty($otherRoomIds)) {
                            // If primary, set other mappings for these rooms as non-primary
                            if (!empty($data['is_primary'])) {
                                RoomTouristSpotMap::query()
                                    ->whereIn('room_id', $otherRoomIds)
                                    ->update(['is_primary' => false]);
                            }

                            // Upsert mapping for all other rooms
                            foreach ($otherRoomIds as $otherRoomId) {
                                RoomTouristSpotMap::query()->updateOrCreate(
                                    [
                                        'room_id' => $otherRoomId,
                                        'tourist_spot_id' => $touristSpotId,
                                    ],
                                    [
                                        'distance_km' => $data['distance_km'] ?? null,
                                        'travel_time_minutes' => $data['travel_time_minutes'],
                                        'priority_order' => $data['priority_order'] ?? 0,
                                        'is_primary' => !empty($data['is_primary']),
                                        'note' => $data['note'] ?? null,
                                        'source_type' => 'manual',
                                    ]
                                );
                            }
                        }
                    }
                }

                return $mainMap;
            });

            return [
                'success' => true,
                'data' => $map->fresh(['room', 'touristSpot']),
                'message' => 'Tạo mapping điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error creating room tourist spot map: ' . $throwable->getMessage(), [
                'data' => $data,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Tạo mapping điểm du lịch thất bại.',
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $applyToAllRooms = !empty($data['apply_to_all_rooms']);
            unset($data['apply_to_all_rooms']);

            $data['source_type'] = 'manual';
            $map = RoomTouristSpotMap::query()->find($id);

            if (! $map) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy mapping điểm du lịch.',
                ];
            }

            DB::transaction(function () use ($map, $data, $applyToAllRooms): void {
                // If updating this mapping to be primary, clear primary flag for all other mappings of this room
                if (!empty($data['is_primary'])) {
                    RoomTouristSpotMap::query()
                        ->where('room_id', (int) $map->room_id)
                        ->where('id', '!=', $map->id)
                        ->update(['is_primary' => false]);
                }

                $map->update($data);

                if ($applyToAllRooms) {
                    $roomId = (int) $map->room_id;
                    $touristSpotId = (int) $map->tourist_spot_id;

                    // Get property ID of the current room
                    $propertyId = DB::table('rooms')->where('id', $roomId)->value('property_id');
                    if ($propertyId) {
                        // Find all other rooms in the same property
                        $otherRoomIds = DB::table('rooms')
                            ->where('property_id', $propertyId)
                            ->where('id', '!=', $roomId)
                            ->pluck('id')
                            ->toArray();

                        if (!empty($otherRoomIds)) {
                            // If primary, set other mappings for these rooms as non-primary
                            if (!empty($data['is_primary'])) {
                                RoomTouristSpotMap::query()
                                    ->whereIn('room_id', $otherRoomIds)
                                    ->update(['is_primary' => false]);
                            }

                            // Propagate changes to other rooms
                            foreach ($otherRoomIds as $otherRoomId) {
                                RoomTouristSpotMap::query()->updateOrCreate(
                                    [
                                        'room_id' => $otherRoomId,
                                        'tourist_spot_id' => $touristSpotId,
                                    ],
                                    [
                                        'distance_km' => array_key_exists('distance_km', $data)
                                            ? $data['distance_km']
                                            : $map->distance_km,
                                        'travel_time_minutes' => array_key_exists('travel_time_minutes', $data)
                                            ? $data['travel_time_minutes']
                                            : $map->travel_time_minutes,
                                        'priority_order' => array_key_exists('priority_order', $data)
                                            ? $data['priority_order']
                                            : $map->priority_order,
                                        'is_primary' => array_key_exists('is_primary', $data)
                                            ? !empty($data['is_primary'])
                                            : $map->is_primary,
                                        'note' => array_key_exists('note', $data) ? $data['note'] : $map->note,
                                        'source_type' => 'manual',
                                    ]
                                );
                            }
                        }
                    }
                }
            });

            return [
                'success' => true,
                'data' => $map->fresh(['room', 'touristSpot']),
                'message' => 'Cập nhật mapping điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error updating room tourist spot map: ' . $throwable->getMessage(), [
                'id' => $id,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Cập nhật mapping điểm du lịch thất bại.',
            ];
        }
    }

    public function destroy(int $id, array $options = []): array
    {
        try {
            $map = RoomTouristSpotMap::query()->find($id);

            if (! $map) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy mapping điểm du lịch.',
                ];
            }

            $applyToAllRooms = !empty($options['apply_to_all_rooms']);

            DB::transaction(static function () use ($map, $applyToAllRooms): void {
                if ($applyToAllRooms) {
                    $roomId = (int) $map->room_id;
                    $touristSpotId = (int) $map->tourist_spot_id;

                    // Get property ID of the current room
                    $propertyId = DB::table('rooms')->where('id', $roomId)->value('property_id');
                    if ($propertyId) {
                        $roomIdsInProperty = DB::table('rooms')
                            ->where('property_id', $propertyId)
                            ->pluck('id')
                            ->toArray();

                        if (!empty($roomIdsInProperty)) {
                            RoomTouristSpotMap::query()
                                ->whereIn('room_id', $roomIdsInProperty)
                                ->where('tourist_spot_id', $touristSpotId)
                                ->delete();
                        }
                    }
                } else {
                    $map->delete();
                }
            });

            return [
                'success' => true,
                'data' => null,
                'message' => 'Xóa mapping điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error deleting room tourist spot map: ' . $throwable->getMessage(), [
                'id' => $id,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Xóa mapping điểm du lịch thất bại.',
            ];
        }
    }

    public function detail(int $id): array
    {
        try {
            $map = RoomTouristSpotMap::query()->with(['room', 'touristSpot'])->find($id);

            if (! $map) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy mapping điểm du lịch.',
                ];
            }

            return [
                'success' => true,
                'data' => $map,
                'message' => 'Lấy mapping điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error fetching room tourist spot map: ' . $throwable->getMessage(), [
                'id' => $id,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Lấy mapping điểm du lịch thất bại.',
            ];
        }
    }

    private function ensureSinglePrimary(int $roomId, bool $isPrimary, ?int $ignoreId = null): void
    {
        if (! $isPrimary) {
            return;
        }

        $query = RoomTouristSpotMap::query()
            ->where('room_id', $roomId)
            ->where('is_primary', true);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw new \RuntimeException('Mỗi phòng chỉ được có một điểm du lịch chính.');
        }
    }
}

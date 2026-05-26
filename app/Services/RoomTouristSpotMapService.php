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
                ->orderBy('priority_order')
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
            $this->ensureSinglePrimary((int) $data['room_id'], ! empty($data['is_primary']));

            $map = DB::transaction(static function () use ($data): RoomTouristSpotMap {
                return RoomTouristSpotMap::query()->create($data);
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
            $map = RoomTouristSpotMap::query()->find($id);

            if (! $map) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy mapping điểm du lịch.',
                ];
            }

            if (array_key_exists('is_primary', $data)) {
                $this->ensureSinglePrimary((int) $map->room_id, (bool) $data['is_primary'], $id);
            }

            DB::transaction(static function () use ($map, $data): void {
                $map->update($data);
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

    public function destroy(int $id): array
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

            $map->delete();

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

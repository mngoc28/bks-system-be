<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TouristSpot;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class TouristSpotService
{
    public function index(Request $request): array
    {
        try {
            $query = TouristSpot::query();

            if ($request->filled('keyword')) {
                $keyword = (string) $request->input('keyword');
                $query->where(function ($builder) use ($keyword): void {
                    $builder->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('slug', 'like', '%' . $keyword . '%');
                });
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
            }

            if ($request->filled('province_id')) {
                $query->where('province_id', (int) $request->input('province_id'));
            }

            $spots = $query->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('id', 'desc')
                ->paginate((int) $request->input('per_page', config('const.DEFAULT_PER_PAGE')));

            return [
                'success' => true,
                'data' => $spots,
                'message' => 'Lấy danh sách điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error listing tourist spots: ' . $throwable->getMessage(), [
                'trace' => $throwable->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Lấy danh sách điểm du lịch thất bại.',
            ];
        }
    }

    public function store(array $data): array
    {
        try {
            $spot = DB::transaction(static function () use ($data): TouristSpot {
                return TouristSpot::query()->create($data);
            });

            app(HomePageCacheService::class)->bumpMetadataCacheVersion();

            return [
                'success' => true,
                'data' => $spot,
                'message' => 'Tạo điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error creating tourist spot: ' . $throwable->getMessage(), [
                'data' => $data,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Tạo điểm du lịch thất bại.',
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $spot = TouristSpot::query()->find($id);

            if (! $spot) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy điểm du lịch.',
                ];
            }

            DB::transaction(static function () use ($spot, $data): void {
                $spot->update($data);
            });

            app(HomePageCacheService::class)->bumpMetadataCacheVersion();

            return [
                'success' => true,
                'data' => $spot->fresh(),
                'message' => 'Cập nhật điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error updating tourist spot: ' . $throwable->getMessage(), [
                'id' => $id,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Cập nhật điểm du lịch thất bại.',
            ];
        }
    }

    public function destroy(int $id): array
    {
        try {
            $spot = TouristSpot::query()->find($id);

            if (! $spot) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy điểm du lịch.',
                ];
            }

            $spot->delete();

            return [
                'success' => true,
                'data' => null,
                'message' => 'Xóa điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error deleting tourist spot: ' . $throwable->getMessage(), [
                'id' => $id,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Xóa điểm du lịch thất bại.',
            ];
        }
    }

    public function detail(int $id): array
    {
        try {
            $spot = TouristSpot::query()->find($id);

            if (! $spot) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy điểm du lịch.',
                ];
            }

            return [
                'success' => true,
                'data' => $spot,
                'message' => 'Lấy điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error fetching tourist spot: ' . $throwable->getMessage(), [
                'id' => $id,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Lấy điểm du lịch thất bại.',
            ];
        }
    }

    public function allActive(): Collection
    {
        return TouristSpot::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Public list for search autocomplete (homepage / room search).
     *
     * @return array{success: bool, data: Collection<int, TouristSpot>|array, message: string}
     */
    public function listPublicSuggestions(Request $request): array
    {
        try {
            $query = TouristSpot::query()->where('is_active', true);

            if ($request->boolean('featured_only')) {
                $query->where('is_featured', true);
            }

            if ($request->filled('keyword')) {
                $keyword = trim((string) $request->input('keyword'));
                $query->where(function ($builder) use ($keyword): void {
                    $builder->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('region_label', 'like', '%' . $keyword . '%')
                        ->orWhere('slug', 'like', '%' . str_replace(' ', '-', $keyword) . '%');
                });
            }

            if ($request->filled('province_id')) {
                $query->where('province_id', (int) $request->input('province_id'));
            }

            $limit = min(max((int) $request->input('limit', 20), 1), 50);

            $spots = $query
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit($limit)
                ->get(['id', 'name', 'slug', 'region_label', 'is_featured', 'category']);

            return [
                'success' => true,
                'data' => $spots,
                'message' => 'Lấy danh sách điểm du lịch thành công.',
            ];
        } catch (\Throwable $throwable) {
            Log::error('Error listing public tourist spot suggestions: ' . $throwable->getMessage());

            return [
                'success' => false,
                'data' => [],
                'message' => 'Lấy danh sách điểm du lịch thất bại.',
            ];
        }
    }
}

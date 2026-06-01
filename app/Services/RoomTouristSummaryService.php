<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RoomTouristSpotMap;
use Illuminate\Support\Collection;

final class RoomTouristSummaryService
{
    public function __construct(
        private readonly RoomTouristGeographyService $geographyService,
    ) {
    }

    public function enrichRooms($rooms)
    {
        if (is_object($rooms) && method_exists($rooms, 'getCollection') && method_exists($rooms, 'setCollection')) {
            $rooms->setCollection($this->enrichRoomCollection($rooms->getCollection()));

            return $rooms;
        }

        return $this->enrichRoomCollection(collect($rooms));
    }

    private function enrichRoomCollection(Collection $roomsCollection): Collection
    {
        $roomIds = $roomsCollection
            ->pluck('id')
            ->filter()
            ->map(static fn ($roomId) => (int) $roomId)
            ->values()
            ->all();

        $summaries = $this->buildSummaryMap($roomIds);

        return $roomsCollection->map(function ($room) use ($summaries) {
            $roomId = (int) ($room->id ?? 0);
            $room->tourist_summary = $summaries[$roomId] ?? $this->defaultSummary();

            return $room;
        });
    }

    public function enrichRoom(?object $room): ?object
    {
        if (! $room) {
            return null;
        }

        $summaryMap = $this->buildSummaryMap([(int) $room->id]);
        $room->tourist_summary = $summaryMap[(int) $room->id] ?? $this->defaultSummary();

        return $room;
    }

    public function buildSummaryMap(array $roomIds): array
    {
        if ($roomIds === []) {
            return [];
        }

        $records = $this->geographyService
            ->applySameProvinceConstraint(
                RoomTouristSpotMap::query()
                    ->with(['touristSpot'])
                    ->whereIn('room_tourist_spot_maps.room_id', $roomIds)
                    ->whereHas('touristSpot', static function ($query): void {
                        $query->where('is_active', true);
                    })
            )
            ->orderByDesc('room_tourist_spot_maps.is_primary')
            ->orderBy('room_tourist_spot_maps.distance_km')
            ->orderBy('room_tourist_spot_maps.travel_time_minutes')
            ->orderByDesc('room_tourist_spot_maps.id')
            ->get()
            ->groupBy('room_id');

        $result = [];

        foreach ($roomIds as $roomId) {
            $result[$roomId] = $this->buildRoomSummary($records->get($roomId, collect()));
        }

        return $result;
    }

    private function buildRoomSummary(Collection $roomRecords): array
    {
        if ($roomRecords->isEmpty()) {
            return $this->defaultSummary();
        }

        $mappedSpots = $roomRecords
            ->filter(static fn (RoomTouristSpotMap $map): bool => (bool) $map->touristSpot)
            ->values();

        if ($mappedSpots->isEmpty()) {
            return $this->defaultSummary();
        }

        $primaryMap = $mappedSpots->firstWhere('is_primary', true) ?? $mappedSpots->first();
        $primarySpot = $primaryMap->touristSpot;
        $allSpots = $mappedSpots->map(fn (RoomTouristSpotMap $map): array => $this->mapSpotEntry($map));

        return [
            'has_tourist_mapping' => true,
            'tourist_spot_name' => (string) ($primarySpot?->name ?? ''),
            'tourist_spot_slug' => $primarySpot?->slug,
            'travel_time_label' => $this->formatTravelTime((int) $primaryMap->travel_time_minutes),
            'distance_label' => $this->formatDistance($primaryMap->distance_km),
            'tourist_spots' => $allSpots->values()->all(),
        ];
    }

    private function mapSpotEntry(RoomTouristSpotMap $map): array
    {
        return [
            'id' => $map->touristSpot?->id,
            'name' => (string) ($map->touristSpot?->name ?? ''),
            'slug' => $map->touristSpot?->slug,
            'travel_time_minutes' => (int) $map->travel_time_minutes,
            'travel_time_label' => $this->formatTravelTime((int) $map->travel_time_minutes),
            'distance_km' => $map->distance_km !== null ? (float) $map->distance_km : null,
            'distance_label' => $this->formatDistance($map->distance_km),
            'is_primary' => (bool) $map->is_primary,
        ];
    }

    private function defaultSummary(): array
    {
        return [
            'has_tourist_mapping' => false,
            'tourist_spot_name' => null,
            'tourist_spot_slug' => null,
            'travel_time_label' => null,
            'distance_label' => null,
            'tourist_spots' => [],
        ];
    }

    private function formatTravelTime(int $minutes): ?string
    {
        if ($minutes <= 0) {
            return null;
        }

        return $minutes . ' phút di chuyển';
    }

    private function formatDistance($distanceKm): ?string
    {
        if ($distanceKm === null || $distanceKm === '') {
            return null;
        }

        return rtrim(rtrim(number_format((float) $distanceKm, 2, '.', ''), '0'), '.') . ' km';
    }
}

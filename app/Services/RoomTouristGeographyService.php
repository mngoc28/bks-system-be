<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RoomTouristSpotMap;
use App\Models\TouristSpot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class RoomTouristGeographyService
{
    public function roomMatchesSpotProvince(int $roomId, int $touristSpotId): bool
    {
        $roomProvinceId = $this->resolveRoomProvinceId($roomId);
        $spotProvinceId = $this->resolveSpotProvinceId($touristSpotId);

        if ($roomProvinceId === null || $spotProvinceId === null) {
            return false;
        }

        return $roomProvinceId === $spotProvinceId;
    }

    public function resolveRoomProvinceId(int $roomId): ?int
    {
        $provinceId = DB::table('rooms')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('rooms.id', $roomId)
            ->value('properties.province_id');

        return $provinceId !== null ? (int) $provinceId : null;
    }

    public function resolveSpotProvinceId(int $touristSpotId): ?int
    {
        $spot = TouristSpot::query()->find($touristSpotId);

        if (! $spot || $spot->province_id === null) {
            return null;
        }

        return (int) $spot->province_id;
    }

    /**
     * Restrict map queries to room/spot pairs in the same province.
     *
     * @param Builder<RoomTouristSpotMap> $query
     * @return Builder<RoomTouristSpotMap>
     */
    public function applySameProvinceConstraint(Builder $query): Builder
    {
        return $query
            ->join('rooms as r_geo', 'r_geo.id', '=', 'room_tourist_spot_maps.room_id')
            ->join('properties as prop_geo', 'prop_geo.id', '=', 'r_geo.property_id')
            ->join('tourist_spots as ts_geo', 'ts_geo.id', '=', 'room_tourist_spot_maps.tourist_spot_id')
            ->whereColumn('prop_geo.province_id', 'ts_geo.province_id')
            ->whereNotNull('ts_geo.province_id')
            ->select('room_tourist_spot_maps.*');
    }

    public function countInvalidMaps(): int
    {
        return (int) DB::table('room_tourist_spot_maps as rtsm')
            ->join('rooms', 'rooms.id', '=', 'rtsm.room_id')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->join('tourist_spots as ts', 'ts.id', '=', 'rtsm.tourist_spot_id')
            ->where(function ($query): void {
                $query->whereNull('ts.province_id')
                    ->orWhereRaw('properties.province_id <> ts.province_id');
            })
            ->count();
    }

    public function pruneInvalidMaps(bool $dryRun = true): int
    {
        $invalidIds = DB::table('room_tourist_spot_maps as rtsm')
            ->join('rooms', 'rooms.id', '=', 'rtsm.room_id')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->join('tourist_spots as ts', 'ts.id', '=', 'rtsm.tourist_spot_id')
            ->where(function ($query): void {
                $query->whereNull('ts.province_id')
                    ->orWhereRaw('properties.province_id <> ts.province_id');
            })
            ->pluck('rtsm.id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if ($invalidIds === [] || $dryRun) {
            return count($invalidIds);
        }

        return RoomTouristSpotMap::query()->whereIn('id', $invalidIds)->delete();
    }

    /**
     * Estimate a realistic distance and travel time.
     */
    public function estimateDistanceAndTime(float $minKm = 1.5, float $maxKm = 15.0): array
    {
        // Random distance between $minKm and $maxKm (1 decimal place)
        $distanceKm = round($minKm + mt_rand() / mt_getrandmax() * ($maxKm - $minKm), 1);

        // Travel time based on speed ~ 30 km/h (2 mins per km) + random delay for traffic/lights
        $travelTimeMinutes = (int) round(($distanceKm * 2) + rand(3, 8));

        return [
            'distance_km' => $distanceKm,
            'travel_time_minutes' => $travelTimeMinutes,
        ];
    }

    /**
     * Automatically map a room to tourist spots in the same province.
     */
    public function autoMapRoomToTouristSpots(int $roomId): void
    {
        $provinceId = $this->resolveRoomProvinceId($roomId);
        if ($provinceId === null) {
            return;
        }

        // Get active tourist spots in the same province
        $spots = DB::table('tourist_spots')
            ->where('province_id', $provinceId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id']);

        if ($spots->isEmpty()) {
            return;
        }

        // Check if maps already exist
        $exists = DB::table('room_tourist_spot_maps')
            ->where('room_id', $roomId)
            ->exists();

        if ($exists) {
            return;
        }

        $maps = [];
        $isPrimary = true; // Mark the first one as primary
        $priority = 1;

        foreach ($spots as $spot) {
            $estimate = $this->estimateDistanceAndTime();

            $maps[] = [
                'room_id' => $roomId,
                'tourist_spot_id' => $spot->id,
                'distance_km' => $estimate['distance_km'],
                'travel_time_minutes' => $estimate['travel_time_minutes'],
                'priority_order' => $priority++,
                'is_primary' => $isPrimary,
                'source_type' => 'estimated',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $isPrimary = false; // Only first spot is primary
        }

        if ($maps !== []) {
            DB::table('room_tourist_spot_maps')->insert($maps);
        }
    }
}

<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RoomStatus;
use App\Services\RoomTouristGeographyService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomTouristSpotMapsTableSeeder extends Seeder
{
    private const MVP_SPOT_SLUGS = [
        'ho-hoan-kiem',
        'cho-ben-thanh',
        'sa-pa',
        'cat-ba',
        'ly-son',
        'ba-na-hill',
        'bai-bien-my-khe',
        'vinh-ha-long',
        'vinwonders-nha-trang',
        'ho-xuan-huong',
        'dai-noi-hue',
        'trang-an',
    ];

    /** Align with homepage default: GET home/rooms/rooms-by-tourist-spot?limit=12 */
    private const MIN_ROOMS_PER_SPOT = 12;

    public function run(RoomTouristGeographyService $geographyService): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('room_tourist_spot_maps')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $spots = DB::table('tourist_spots')
            ->whereIn('slug', self::MVP_SPOT_SLUGS)
            ->where('is_active', true)
            ->whereNotNull('province_id')
            ->get(['id', 'slug', 'province_id']);

        if ($spots->isEmpty()) {
            return;
        }

        $maps = [];

        foreach ($spots as $spot) {
            $provinceId = (int) $spot->province_id;

            $roomIds = DB::table('rooms')
                ->join('properties', 'properties.id', '=', 'rooms.property_id')
                ->where('rooms.status', RoomStatus::PUBLIC)
                ->where('properties.province_id', $provinceId)
                ->orderBy('rooms.id')
                ->pluck('rooms.id')
                ->all();

            if (count($roomIds) < self::MIN_ROOMS_PER_SPOT) {
                Log::warning('RoomTouristSpotMapsTableSeeder: not enough PUBLIC rooms for spot', [
                    'slug' => $spot->slug,
                    'province_id' => $provinceId,
                    'available' => count($roomIds),
                    'required' => self::MIN_ROOMS_PER_SPOT,
                ]);
            }

            $assigned = 0;
            $poolSize = count($roomIds);

            if ($poolSize === 0) {
                continue;
            }

            for ($i = 0; $i < self::MIN_ROOMS_PER_SPOT; $i++) {
                $roomId = (int) $roomIds[$i % $poolSize];
                
                $estimate = $geographyService->estimateDistanceAndTime();

                $maps[] = [
                    'room_id' => $roomId,
                    'tourist_spot_id' => (int) $spot->id,
                    'distance_km' => $estimate['distance_km'],
                    'travel_time_minutes' => $estimate['travel_time_minutes'],
                    'priority_order' => $i + 1,
                    'is_primary' => $i === 0,
                    'source_type' => 'estimated',
                    'note' => 'Generated automatically by Seeder',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $assigned++;
            }
        }

        if ($maps !== []) {
            DB::table('room_tourist_spot_maps')->insert($maps);
        }
    }
}

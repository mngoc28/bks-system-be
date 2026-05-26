<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomTouristSpotMapsTableSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('room_tourist_spot_maps')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $spots = DB::table('tourist_spots')->pluck('id')->toArray();

        if (empty($spots)) {
            return;
        }

        // attach first few rooms to the first tourist spot(s)
        $rooms = DB::table('rooms')->limit(10)->pluck('id')->toArray();
        $maps = [];
        foreach ($rooms as $idx => $roomId) {
            $maps[] = [
                'room_id' => $roomId,
                'tourist_spot_id' => $spots[$idx % count($spots)],
                'distance_km' => rand(1, 15),
                'travel_time_minutes' => rand(5, 60),
                'priority_order' => $idx + 1,
                'is_primary' => ($idx % 3) === 0,
                'source_type' => 'manual',
                'note' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($maps)) {
            DB::table('room_tourist_spot_maps')->insert($maps);
        }
    }
}

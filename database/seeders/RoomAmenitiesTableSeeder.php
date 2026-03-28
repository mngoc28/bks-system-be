<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RoomAmenitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('room_amenities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $roomIds = DB::table('rooms')->pluck('id')->toArray();
        $amenityIds = DB::table('amenities')->pluck('id')->toArray();

        if (empty($roomIds) || empty($amenityIds)) {
            $this->command->warn('No rooms or amenities found. Please run RoomsTableSeeder and AmenitiesTableSeeder first.');
            return;
        }

        $pairs = collect();

        // Each room should have 5-15 amenities
        foreach ($roomIds as $roomId) {
            $numAmenities = rand(5, 15);
            $selectedAmenities = $faker->randomElements($amenityIds, $numAmenities);

            foreach ($selectedAmenities as $amenityId) {
                $pairs->push([
                    'room_id' => $roomId,
                    'amenity_id' => $amenityId,
                    'created_by' => $faker->randomElement($adminPartnerIds),
                    'updated_by' => $faker->randomElement($adminPartnerIds),
                    'created_at' => Carbon::now()->subDays(rand(1, 40)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 40)),
                ]);
            }
        }

        // Insert in chunks to avoid memory issues
        $pairs->chunk(100)->each(function ($chunk) {
            DB::table('room_amenities')->insert($chunk->toArray());
        });
    }
}


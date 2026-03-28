<?php

declare (strict_types = 1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RoomServiceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('room_services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $roomIds    = DB::table('rooms')->pluck('id')->toArray();
        $serviceIds = DB::table('services')->pluck('id')->toArray();

        if (empty($roomIds) || empty($serviceIds)) {
            $this->command->warn('No rooms or services found. Please run RoomsTableSeeder and ServicesTableSeeder first.');
            return;
        }

        $pairs = collect();

        // Each room should have 3-10 services
        foreach ($roomIds as $roomId) {
            $numServices      = rand(3, 10);
            $selectedServices = $faker->randomElements($serviceIds, min($numServices, count($serviceIds)));

            foreach ($selectedServices as $serviceId) {
                $pairs->push([
                    'room_id'    => $roomId,
                    'service_id' => $serviceId,
                    'created_by' => $faker->randomElement($adminPartnerIds),
                    'updated_by' => $faker->randomElement($adminPartnerIds),
                    'created_at' => Carbon::now()->subDays(rand(1, 40)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 40)),
                ]);
            }
        }

        // Insert in chunks to avoid memory issues
        $pairs->chunk(100)->each(function ($chunk) {
            DB::table('room_services')->insert($chunk->toArray());
        });
    }
}

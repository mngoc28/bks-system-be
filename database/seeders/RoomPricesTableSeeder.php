<?php

declare (strict_types = 1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RoomPricesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('room_prices')->truncate();
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
        $packageIds = DB::table('price_packages')->pluck('id')->toArray();

        if (empty($roomIds) || empty($packageIds)) {
            $this->command->warn('No rooms or price packages found. Please run RoomsTableSeeder and PricePackagesTableSeeder first.');
            return;
        }

        $pairs = collect();
        $units = ['day', 'month'];

        // Each room has 1-4 prices (packages vary per room)
        foreach ($roomIds as $roomId) {
            $room = DB::table('rooms')->where('id', $roomId)->first();
            // Calculate base price based on area (if available) or use default
            $area      = $room ? (float) $room->area : 25.0;
            $randomFactor = $faker->randomFloat(2, 0.8, 1.2);
            $basePrice = (int) ($area * 150000 * $randomFactor); // ~150k per m² per day

            // Randomly select 1-4 packages for this room
            $selectedPackages = collect($packageIds)->shuffle()->take(rand(1, 4))->toArray();

            foreach ($selectedPackages as $packageId) {
                // Price varies by package
                $packageMultiplier = match ($packageId) {
                    1       => 0.7,  // super small - 30% discount
                    2       => 0.85, // small - 15% discount
                    3       => 1.0,  // medium - base price
                    4       => 1.3,  // large - 30% premium
                    default => 1.0,
                };

                // Randomly select unit (day or month)
                $unit = $faker->randomElement($units);
                $multiplier = match ($unit) {
                    'day'   => 1,
                    'month' => 28,
                    default => 1,
                };

                $price = (int) ($basePrice * $packageMultiplier * $multiplier);

                $pairs->push([
                    'room_id'          => $roomId,
                    'price_package_id' => $packageId,
                    'unit'             => $unit,
                    'price'            => $price,
                    'created_by'       => $faker->randomElement($adminPartnerIds),
                    'updated_by'       => $faker->randomElement($adminPartnerIds),
                    'created_at'       => Carbon::now()->subDays(rand(1, 40)),
                    'updated_at'       => Carbon::now()->subDays(rand(1, 40)),
                ]);
            }
        }

        // Insert in chunks to avoid memory issues
        $pairs->chunk(100)->each(function ($chunk) {
            DB::table('room_prices')->insert($chunk->toArray());
        });
    }
}
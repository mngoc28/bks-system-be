<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            ProvincesTableSeeder::class,
            WardsTableSeeder::class,
            PartnerInfoTableSeeder::class,
            AmenitiesTableSeeder::class,
            PricePackagesTableSeeder::class,
            NewsTableSeeder::class,
            BuildingsTableSeeder::class,
            RoomsTableSeeder::class,
            ServicesTableSeeder::class,
            BuildingImagesTableSeeder::class,
            // RoomImagesTableSeeder::class,
            RoomAmenitiesTableSeeder::class,
            RoomServiceTableSeeder::class,
            RoomPricesTableSeeder::class,
            BookingsTableSeeder::class,
            ChatbotSeeder::class,
            PropertyTypesSeeder::class,
            RoomMaintenancesSeeder::class,
        ]);
    }
}

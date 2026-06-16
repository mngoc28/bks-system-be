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
            PropertyTypesSeeder::class,
            PartnerInfoTableSeeder::class,
            AmenitiesTableSeeder::class,
            PricePackagesTableSeeder::class,
            NewsTableSeeder::class,
            PropertiesTableSeeder::class,
            RoomsTableSeeder::class,
            PropertyImagesTableSeeder::class,
            RoomImagesTableSeeder::class,
            TouristSpotsTableSeeder::class,
            RoomTouristSpotMapsTableSeeder::class,
            ServicesTableSeeder::class,
            RoomAmenitiesTableSeeder::class,
            RoomServiceTableSeeder::class,
            RoomPricesTableSeeder::class,
            BookingsTableSeeder::class,
            ChatbotSeeder::class,
            RoomMaintenancesSeeder::class,
            StayPortalSeeder::class,
            CancellationReasonCodesSeeder::class,
            CancellationPolicyBaselineSeeder::class,
            ReviewsTableSeeder::class,
            PartnerSettlementsSeeder::class,
            HospitalityRulesUpdateSeeder::class,
        ]);

        $this->command?->info('Đang khôi phục ảnh từ Cloudinary...');
        \Illuminate\Support\Facades\Artisan::call('cloudinary:restore');
    }
}

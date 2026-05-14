<?php

declare (strict_types = 1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class PropertyImagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('property_images')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        $propertyIds = DB::table('properties')->pluck('id')->toArray();

        if (empty($propertyIds)) {
            $this->command->warn('No properties found. Please run PropertiesTableSeeder first.');
            return;
        }

        // image_type: 0=main_property, 1=exterior, 2=interior, 3=bathroom, 4=kitchen
        $imageTypeNames = ['main', 'exterior', 'interior', 'bathroom', 'kitchen'];

        foreach ($propertyIds as $propertyId) {
            // Each property should have 3-8 images
            $numImages = rand(3, 8);
            $sort      = 1;

            // Always have at least one main image (type = 0)
            DB::table('property_images')->insert([
                'property_id'          => $propertyId,
                'image_url'            => '/images/properties/property_' . $propertyId . '_main.jpg',
                'id_image_cloudinary'  => 'properties/property_' . $propertyId . '_main',
                'image_type'           => 0, // main_property
                'sort'                 => $sort++,
                'created_by'           => $faker->randomElement($adminPartnerIds),
                'updated_by'           => $faker->randomElement($adminPartnerIds),
                'created_at'           => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'           => Carbon::now()->subDays(rand(1, 40)),
            ]);

            // Add other images
            for ($i = 1; $i < $numImages; $i++) {
                $imageType     = $faker->numberBetween(0, 4); // 0-4
                $imageTypeName = $imageTypeNames[$imageType];
                $imageIndex    = $i;

                DB::table('property_images')->insert([
                    'property_id'         => $propertyId,
                    'image_url'           => '/images/properties/property_' . $propertyId . '_' . $imageTypeName . '_' . $imageIndex . '.jpg',
                    'id_image_cloudinary' => 'properties/property_' . $propertyId . '_' . $imageTypeName . '_' . $imageIndex,
                    'image_type'          => $imageType,
                    'sort'                => $sort++,
                    'created_by'          => $faker->randomElement($adminPartnerIds),
                    'updated_by'          => $faker->randomElement($adminPartnerIds),
                    'created_at'          => Carbon::now()->subDays(rand(1, 40)),
                    'updated_at'          => Carbon::now()->subDays(rand(1, 40)),
                ]);
            }
        }
    }
}


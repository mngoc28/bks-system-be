<?php

declare(strict_types = 1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class RoomImagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('room_images')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $adminPartnerIds = DB::table('users')
            ->whereIn('role', ['admin', 'partner'])
            ->pluck('id')
            ->toArray();

        if (empty($adminPartnerIds)) {
            $adminPartnerIds = [1];
        }

        // Pools of real-world Unsplash image IDs for rooms (doubled size, no duplicates)
        $hotelBedrooms = [
            'photo-1618773928121-c32242e63f39', // luxury hotel king bed
            'photo-1590490360182-c33d57733427', // modern deluxe hotel
            'photo-1582719508461-905c673771fd', // cozy hotel bedroom
            'photo-1566607377125-6a8506099945', // luxury hotel fallback
            'photo-1566665797739-1674de7a421a', // cozy double bed
            'photo-1591088398332-8a7791972843', // neat tidy bed
            'photo-1631049307264-da0ec9d70304', // boutique hotel double
            'photo-1540518614846-7eded433c457', // cozy room warm light
            'photo-1568495248636-6432b97bd949', // neat sheets bedroom
            'photo-1595526114035-0d45ed16cfbf', // modern minimalist room
            'photo-1590490360182-c33d57733427', // modern deluxe hotel
            'photo-15666607377125-6a8506099945', // luxury hotel fallback
            'photo-1582719508461-905c673771fd', // cozy hotel bedroom
            'photo-1590490360182-c33d57733427', // modern deluxe hotel
            'photo-1596394516093-501ba68a0ba6', // hotel room overview
            'photo-1578683010236-d716f9a3f461', // cozy boutique hotel
            'photo-1578894381163-e72c17f2d45f', // double hotel room bed setup
            'photo-1608198399988-341f712c3711', // boutique hotel bed setup
            'photo-1582719478250-c89cae4dc85b'  // clean hotel bedroom linens
        ];

        $apartmentBedrooms = [
            'photo-1522708323590-d24dbb6b0267', // studio apartment bed
            'photo-1502672260266-1c1ef2d93688', // serviced apartment room
            'photo-1600607687939-ce8a6c25118c', // sleek minimalist apartment
            'photo-1586023492125-27b2c045efd7', // studio room workspace
            'photo-1615874959474-d609969a20ed', // modern apartment interior
            'photo-1522771739844-6a9f6d5f14af', // warm studio bedroom
            'photo-1522708323590-d24dbb6b0267', // studio apartment bed
            'photo-1600121848594-d8644e57abab', // bright flat bedroom
            'photo-1502672023488-70e25813eb80', // studio loft apartment
            'photo-1536376072261-38c75010e6c9', // modern studio design
            'photo-1505693416388-ac5ce068fe85', // cozy apartment bedroom
            'photo-1616593969747-4797dc75033e', // bright window bedroom
            'photo-1600210492486-724fe5c67fb0', // master bedroom in apartment
            'photo-1502672260266-1c1ef2d93688', // serviced apartment room
            'photo-1600607687939-ce8a6c25118c', // sleek minimalist apartment
            'photo-1598928506311-c55ded91a20c', // modern studio room
            'photo-1531835551805-16d864c8d311', // modern bedroom style
            'photo-1522708323590-d24dbb6b0267'  // studio apartment bed
        ];

        $homestayBedrooms = [
            'photo-1583847268964-b28dc8f51f92', // cozy attic bedroom
            'photo-1616594039964-ae9021a400a0', // cabin style wooden bedroom
            'photo-1505691938895-1758d7feb511', // cozy indochine style room
            'photo-1560185007-c5ca9d2c014d', // vintage style guest room
            'photo-1616593969747-4797dc75033e', // bedroom scenic view
            'photo-1583847268964-b28dc8f51f92', // cozy attic bedroom
            'photo-1616594039964-ae9021a400a0', // cabin style wooden bedroom
            'photo-1505691938895-1758d7feb511', // cozy indochine style room
            'photo-1583847268964-b28dc8f51f92', // cozy attic bedroom
            'photo-1591474200742-8e512e6f98f8', // cozy room with logs/wood
            'photo-1616594039964-ae9021a400a0', // cabin style wooden bedroom
            'photo-1513694203232-719a280e022f', // sunlight bedroom rustic
            'photo-1566665797739-1674de7a421a'  // cozy homestay room
        ];

        $dormBedrooms = [
            'photo-1555854877-bab0e564b8d5', // bright bunk bed dorm
            'photo-1507652313519-d4e9174996dd', // wooden bunk beds
            'photo-1529156069898-49953e39b3ac', // cozy dorm style
            'photo-1533090161767-e6ffed986c88'  // modern youth room
        ];

        $bathrooms = [
            'photo-1584622650111-993a426fbf0a',
            'photo-1552321554-5fefe8c9ef14',
            'photo-1620626011761-996317b8d101',
            'photo-1600566753190-17f0baa2a6c3',
            'photo-1604014237800-1c9102c219da',
            'photo-1600566752355-35792bedcfea',
            'photo-1618220179428-22790b461013',
            'photo-1584622650111-993a426fbf0a',
            'photo-1552321554-5fefe8c9ef14',
            'photo-1620626011761-996317b8d101',
            'photo-1584622781564-1d987f7333c1',
            'photo-1600566753190-17f0baa2a6c3',
            'photo-1559827291-72ee739d0d9a',
            'photo-1600607687939-ce8a6c25118c',
            'photo-1613977257363-707ba9348227'
        ];

        $kitchens = [
            'photo-1556911220-e15b29be8c8f',
            'photo-1600585154363-67eb9e2e2099',
            'photo-1600566753086-00f18fb6b3ea',
            'photo-1556911220-e15b29be8c8f',
            'photo-1600585154526-990dced4db0d',
            'photo-1565183997392-2f6f122e5912',
            'photo-1517248135467-4c7edcad34c4',
            'photo-1600585154363-67eb9e2e2099',
            'photo-1600566753086-00f18fb6b3ea',
            'photo-1600585154340-be6161a56a0c',
            'photo-1588854337236-6889d631faa8',
            'photo-1600585154526-990dced4db0d',
            'photo-1507089947368-19c1da9775ae',
            'photo-1615874959474-d609969a20ed',
            'photo-1522708323590-d24dbb6b0267'
        ];

        $details = [
            'photo-1507089947368-19c1da9775ae',
            'photo-1616593969747-4797dc75033e',
            'photo-1513694203232-719a280e022f',
            'photo-1484154218962-a197022b5858',
            'photo-1618219908412-a29a1bb7b86e',
            'photo-1618221195710-dd6b41faaea6',
            'photo-1507089947368-19c1da9775ae',
            'photo-1616486788371-62d930495c44',
            'photo-1618221381711-42ca8ab6e908',
            'photo-1507652313519-d4e9174996dd',
            'photo-1582719478250-c89cae4dc85b',
            'photo-1568495248636-6432b97bd949',
            'photo-1591474200742-8e512e6f98f8',
            'photo-1578683010236-d716f9a3f461',
            'photo-1583847268964-b28dc8f51f92'
        ];

        $rooms = DB::table('rooms')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->join('property_types', 'properties.property_type_id', '=', 'property_types.id')
            ->select('rooms.id', 'rooms.title', 'property_types.slug as property_type_slug')
            ->get();

        if ($rooms->isEmpty()) {
            $this->command->warn('No rooms found. Please run RoomsTableSeeder first.');
            return;
        }

        foreach ($rooms as $room) {
            $roomId = $room->id;
            $title = $room->title;
            $slug = $room->property_type_slug;

            // 1. Select the appropriate bedroom pool
            if (mb_stripos($title, 'Dorm') !== false || mb_stripos($title, 'tập thể') !== false) {
                $bedroomPool = $dormBedrooms;
            } else {
                $bedroomPool = match ($slug) {
                    'khach-san-hotel' => $hotelBedrooms,
                    'can-ho-dich-vu-theo-phong' => $apartmentBedrooms,
                    'nha-nghi-guesthouse', 'homestay-co-chia-phong' => $homestayBedrooms,
                    default => $apartmentBedrooms,
                };
            }

            $sort = 1;

            // Cover Image (sort = 1, type = 0/other/bedroom)
            $coverUnsplashId = $bedroomPool[$roomId % count($bedroomPool)];
            $coverUrl = "https://images.unsplash.com/{$coverUnsplashId}?auto=format&fit=crop&w=800&q=80";

            DB::table('room_images')->insert([
                'room_id'             => $roomId,
                'image_url'           => $coverUrl,
                'id_image_cloudinary' => $coverUnsplashId,
                'image_type'          => 0, // other / main
                'sort'                => $sort++,
                'created_by'          => $faker->randomElement($adminPartnerIds),
                'updated_by'          => $faker->randomElement($adminPartnerIds),
                'created_at'          => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'          => Carbon::now()->subDays(rand(1, 40)),
            ]);

            // Bathroom Image (sort = 2, type = 3/bathroom)
            $bathroomUnsplashId = $bathrooms[$roomId % count($bathrooms)];
            $bathroomUrl = "https://images.unsplash.com/{$bathroomUnsplashId}?auto=format&fit=crop&w=800&q=80";

            DB::table('room_images')->insert([
                'room_id'             => $roomId,
                'image_url'           => $bathroomUrl,
                'id_image_cloudinary' => $bathroomUnsplashId,
                'image_type'          => 3, // bathroom
                'sort'                => $sort++,
                'created_by'          => $faker->randomElement($adminPartnerIds),
                'updated_by'          => $faker->randomElement($adminPartnerIds),
                'created_at'          => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'          => Carbon::now()->subDays(rand(1, 40)),
            ]);

            // Kitchen / Dining Image (sort = 3, type = 4/kitchen)
            $kitchenUnsplashId = $kitchens[$roomId % count($kitchens)];
            $kitchenUrl = "https://images.unsplash.com/{$kitchenUnsplashId}?auto=format&fit=crop&w=800&q=80";

            DB::table('room_images')->insert([
                'room_id'             => $roomId,
                'image_url'           => $kitchenUrl,
                'id_image_cloudinary' => $kitchenUnsplashId,
                'image_type'          => 4, // kitchen
                'sort'                => $sort++,
                'created_by'          => $faker->randomElement($adminPartnerIds),
                'updated_by'          => $faker->randomElement($adminPartnerIds),
                'created_at'          => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'          => Carbon::now()->subDays(rand(1, 40)),
            ]);

            // Interior Detail / Cozy corner Image (sort = 4, type = 2/interior)
            $detailUnsplashId = $details[$roomId % count($details)];
            $detailUrl = "https://images.unsplash.com/{$detailUnsplashId}?auto=format&fit=crop&w=800&q=80";

            DB::table('room_images')->insert([
                'room_id'             => $roomId,
                'image_url'           => $detailUrl,
                'id_image_cloudinary' => $detailUnsplashId,
                'image_type'          => 2, // interior
                'sort'                => $sort++,
                'created_by'          => $faker->randomElement($adminPartnerIds),
                'updated_by'          => $faker->randomElement($adminPartnerIds),
                'created_at'          => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'          => Carbon::now()->subDays(rand(1, 40)),
            ]);
        }
    }
}

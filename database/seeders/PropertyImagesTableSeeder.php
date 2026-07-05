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

        // Real-world Unsplash image pools for properties (doubled size, no duplicates)
        $exteriors = [
            'photo-1564013799919-ab600027ffc6',
            'photo-1580587771525-78b9dba3b914',
            'photo-1512917774080-9991f1c4c750',
            'photo-1600585154526-990dced4db0d',
            'photo-1600596542815-ffad4c1539a9',
            'photo-1600607687920-4e2a09cf159d',
            'photo-1600585154340-be6161a56a0c',
            'photo-1605276374104-dee2a0ed3cd6',
            'photo-1542314831-068cd1dbfeeb',
            'photo-1566073771259-6a8506099945',
            'photo-1520250497591-112f2f40a3f4',
            'photo-1596394516093-501ba68a0ba6',
            'photo-1568605114967-8130f3a36994',
            'photo-1512918728675-ed5a9ecdebfd',
            'photo-1513584684374-8bab748fbf90',
            'photo-1600596542825-76bc4024e16d',
            'photo-1600607687644-c7171b42498f',
            'photo-1583608205776-bfd35f0d9f83',
            'photo-1592595896551-12b371d546d5',
            'photo-1613490493576-7fde63acd811',
            'photo-1613977257363-707ba9348227',
            'photo-1602941525421-8f8b81d3edbb',
            'photo-1571896349842-33c89424de2d',
            'photo-1584132967334-10e028bd69f7',
            'photo-1549294413-26f195afcbce',
            'photo-1560185127-6a2806647f81',
            'photo-1523217582562-09d0def993a6',
            'photo-1512915922686-57c11dde9b6b',
            'photo-1600566753376-12c8ab7fb75b'
        ];

        $interiors = [
            'photo-1571003123894-1f0594d2b5d9',
            'photo-1566073771259-6a8506099945',
            'photo-1590490360182-c33d57733427',
            'photo-1582719508461-905c673771fd',
            'photo-1507089947368-19c1da9775ae',
            'photo-1513694203232-719a280e022f',
            'photo-1484154218962-a197022b5858',
            'photo-1600607687920-4e2a09cf159d',
            'photo-1618219908412-a29a1bb7b86e',
            'photo-1618221195710-dd6b41faaea6',
            'photo-1556912173-3bb406ef7e77',
            'photo-1600210492493-0946911123ea',
            'photo-1600607687939-ce8a6c25118c',
            'photo-1616486038856-3c48a313b860',
            'photo-1616486788371-62d930495c44',
            'photo-1615529182904-14819c35db37',
            'photo-1618221381711-42ca8ab6e908',
            'photo-1616046229478-9901c5536a45',
            'photo-1600566753151-384129cf4e3e',
            'photo-1585412727339-54e4bae3bbf9'
        ];

        $bathrooms = [
            'photo-1584622650111-993a426fbf0a',
            'photo-1552321554-5fefe8c9ef14',
            'photo-1620626011761-996317b8d101',
            'photo-1600566753190-17f0baa2a6c3',
            'photo-1604014237800-1c9102c219da',
            'photo-1600566752355-35792bedcfea',
            'photo-1618220179428-22790b461013',
            'photo-1600566752239-1748286c48f2',
            'photo-1618221494412-657a1006b620',
            'photo-1600566753229-87c1cb383cf8',
            'photo-1584622781564-1d987f7333c1',
            'photo-1630835425197-50feebd99e12',
            'photo-1559827291-72ee739d0d9a',
            'photo-1600607687939-ce8a6c25118c',
            'photo-1613977257363-707ba9348227'
        ];

        $kitchens = [
            'photo-1556911220-e15b29be8c8f',
            'photo-1600585154363-67eb9e2e2099',
            'photo-1600566753086-00f18fb6b3ea',
            'photo-1556911220-115f74bb01db',
            'photo-1600585154526-990dced4db0d',
            'photo-1565183997392-2f6f122e5912',
            'photo-1517248135467-4c7edcad34c4',
            'photo-1600482637209-4b6c8b81c631',
            'photo-1556909212-d5b604ad056f',
            'photo-1600585154340-be6161a56a0c',
            'photo-1588854337236-6889d631faa8',
            'photo-1590247813693-5541f1c0078a',
            'photo-1507089947368-19c1da9775ae',
            'photo-1615874959474-d609969a20ed',
            'photo-1522708323590-d24dbb6b0267'
        ];

        $data = [];
        foreach ($propertyIds as $propertyId) {
            $sort = 1;

            // Pick a main image unique to the property
            $mainUnsplashId = $exteriors[$propertyId % count($exteriors)];
            $mainUrl = "https://images.unsplash.com/{$mainUnsplashId}?auto=format&fit=crop&w=800&q=80";

            // Always have at least one main image (type = 0)
            $data[] = [
                'property_id'          => $propertyId,
                'image_url'            => $mainUrl,
                'id_image_cloudinary'  => $mainUnsplashId,
                'image_type'           => 0, // main_property
                'sort'                 => $sort++,
                'created_by'           => $faker->randomElement($adminPartnerIds),
                'updated_by'           => $faker->randomElement($adminPartnerIds),
                'created_at'           => Carbon::now()->subDays(rand(1, 40)),
                'updated_at'           => Carbon::now()->subDays(rand(1, 40)),
            ];

            // Add other images (1 exterior, 1 interior, 1 bathroom, 1 kitchen)
            $additionalImages = [
                ['pool' => $exteriors, 'type' => 1, 'offset' => 1], // exterior
                ['pool' => $interiors, 'type' => 2, 'offset' => 0], // interior
                ['pool' => $bathrooms, 'type' => 3, 'offset' => 0], // bathroom
                ['pool' => $kitchens, 'type' => 4, 'offset' => 0],  // kitchen
            ];

            foreach ($additionalImages as $add) {
                $pool = $add['pool'];
                $unsplashId = $pool[($propertyId + $add['offset']) % count($pool)];
                $url = "https://images.unsplash.com/{$unsplashId}?auto=format&fit=crop&w=800&q=80";

                $data[] = [
                    'property_id'         => $propertyId,
                    'image_url'           => $url,
                    'id_image_cloudinary' => $unsplashId,
                    'image_type'          => $add['type'],
                    'sort'                => $sort++,
                    'created_by'          => $faker->randomElement($adminPartnerIds),
                    'updated_by'          => $faker->randomElement($adminPartnerIds),
                    'created_at'          => Carbon::now()->subDays(rand(1, 40)),
                    'updated_at'          => Carbon::now()->subDays(rand(1, 40)),
                ];
            }

            if (count($data) >= 500) {
                DB::table('property_images')->insert($data);
                $data = [];
            }
        }

        if (!empty($data)) {
            DB::table('property_images')->insert($data);
        }
    }
}


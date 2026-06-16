<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('rooms')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $faker = Faker::create('vi_VN');

        $descriptions = [
            'Phòng Studio hiện đại với diện tích 25m², thiết kế tối ưu không gian. Phòng được trang bị đầy đủ: giường đôi, tủ quần áo, bàn làm việc, ghế văn phòng, TV 43 inch Smart TV. Khu vực bếp mini với tủ lạnh, lò vi sóng, bếp từ, bồn rửa. Phòng tắm riêng với vòi sen hiện đại, máy sấy tóc. Cửa sổ lớn lấy ánh sáng tự nhiên, view thành phố. Internet wifi tốc độ cao, điều hòa không khí inverter tiết kiệm điện. Phù hợp cho 1-2 người, lý tưởng cho khách công tác hoặc cặp đôi.',
            'Phòng Studio cao cấp 30m² với thiết kế mở rộng không gian. Nội thất gỗ tự nhiên sang trọng, giường King size, sofa bed có thể chuyển thành giường phụ. Khu vực bếp đầy đủ tiện nghi: tủ lạnh mini, lò nướng, máy pha cà phê, bếp từ 2 lò. Phòng tắm rộng với bồn tắm và vòi sen, đồ vệ sinh cao cấp. Ban công riêng, view hướng đông đón nắng sáng. Smart TV 55 inch, hệ thống loa Bluetooth, đèn LED điều chỉnh độ sáng. Internet 1Gbps, điều hòa 2 chiều. Phù hợp cho gia đình nhỏ hoặc khách công tác dài ngày.',
            'Phòng 1 phòng ngủ rộng rãi 35m² với thiết kế hiện đại, tách biệt phòng ngủ và phòng khách. Phòng ngủ có giường đôi lớn, tủ quần áo 3 cánh, bàn trang điểm. Phòng khách với sofa bọc da, bàn trà, TV 55 inch Smart TV, kệ sách. Bếp đầy đủ: tủ lạnh side-by-side, lò vi sóng, lò nướng, bếp từ, máy rửa bát. Phòng tắm rộng với bồn tắm, vòi sen massage, bàn chải răng điện. Cửa sổ kính 2 lớp cách âm, cách nhiệt, ban công rộng view đẹp. Hệ thống điều hòa trung tâm, sàn gỗ, trần thạch cao. Phù hợp cho gia đình 2-3 người.',
            'Phòng 1 phòng ngủ cao cấp 40m² với thiết kế mở, không gian thoáng đãng. Phòng ngủ riêng biệt với giường King size, tủ quần áo tích hợp, bàn làm việc. Khu vực phòng khách rộng với sofa góc, bàn ăn 4 người, TV 65 inch Smart TV 4K. Bếp hiện đại: tủ lạnh lớn, máy rửa bát, máy pha cà phê espresso, lò nướng convection. Phòng tắm luxury với bồn tắm jacuzzi, vòi sen mưa, sàn sưởi. Ban công lớn với bàn ghế ngoài trời, view toàn cảnh thành phố. Smart home: điều khiển đèn, rèm, điều hòa qua điện thoại. Internet tốc độ cao, sàn gỗ cao cấp. Phù hợp cho khách VIP hoặc gia đình nhỏ.',
            'Phòng 2 phòng ngủ rộng 50m², thiết kế tối ưu cho gia đình. Phòng ngủ chính có giường King size, tủ quần áo lớn, bàn trang điểm. Phòng ngủ phụ có 2 giường đơn, tủ quần áo, bàn học. Phòng khách rộng với sofa chữ L, bàn trà, TV 65 inch Smart TV, kệ ti vi. Bếp đầy đủ: tủ lạnh lớn, máy rửa bát, lò vi sóng, lò nướng, bếp từ 4 lò, máy hút mùi. Phòng tắm chính với bồn tắm, phòng tắm phụ với vòi sen. Ban công rộng với bàn ghế, view hướng nam. Điều hòa multi-split, sàn gỗ, cửa sổ kính an toàn. Phù hợp cho gia đình 4-5 người.',
            'Phòng 2 phòng ngủ cao cấp 60m² với thiết kế sang trọng. Phòng ngủ master có giường King size, tủ quần áo walk-in, phòng tắm riêng with bồn tắm jacuzzi. Phòng ngủ phụ có giường đôi, tủ quần áo, bàn làm việc. Phòng khách lớn với sofa da cao cấp, bàn ăn 6 người, TV 75 inch Smart TV 4K, kệ rượu. Bếp hiện đại: tủ lạnh side-by-side, máy rửa bát, máy pha cà phê, lò nướng, bếp từ 5 lò. 2 phòng tắm đầy đủ tiện nghi. Ban công lớn với không gian thư giãn, view đẹp. Hệ thống smart home, điều hòa trung tâm, sàn gỗ cao cấp. Phù hợp cho gia đình lớn hoặc khách VIP.',
        ];

        $properties = DB::table('properties')
            ->join('property_types', 'properties.property_type_id', '=', 'property_types.id')
            ->select('properties.id', 'property_types.slug')
            ->get();

        if ($properties->isEmpty()) {
            return;
        }

        $typeRoomTitles = [
            'khach-san-hotel' => [
                'Phòng Deluxe Giường Đôi Hướng Phố',
                'Phòng Deluxe Giường Đơn Hướng Sân Vườn',
                'Phòng Superior Tiêu Chuẩn',
                'Phòng Superior Hướng Bể Bơi',
                'Phòng Standard Giường Đôi',
                'Phòng Standard Giường Đơn',
                'Phòng Suite Hoàng Gia',
                'Phòng Suite Gia Đình',
                'Phòng Executive Hướng Phố',
                'Phòng Presidential Suite (Tổng Thống)'
            ],
            'nha-nghi-guesthouse' => [
                'Phòng Đơn Tiêu Chuẩn',
                'Phòng Đơn Có Quạt',
                'Phòng Đơn Máy Lạnh',
                'Phòng Đôi Tiêu Chuẩn',
                'Phòng Đôi Hướng Sân',
                'Phòng Đôi Có Ban Công',
                'Phòng Quạt Tiêu Chuẩn',
                'Phòng Điều Hòa Tiện Nghi'
            ],
            'can-ho-dich-vu-theo-phong' => [
                'Phòng Studio Hiện Đại',
                'Phòng Studio Hướng Phố',
                'Phòng Studio Ban Công',
                'Căn Hộ 1 Phòng Ngủ Cozy',
                'Căn Hộ 1 Phòng Ngủ Cao Cấp',
                'Căn Hộ 2 Phòng Ngủ Gia Đình',
                'Căn Hộ 2 Phòng Ngủ View Đẹp',
                'Căn Hộ Studio Tiện Nghi'
            ],
            'homestay-co-chia-phong' => [
                'Phòng Gác Mái Ấm Áp',
                'Phòng Gác Mái Vintage',
                'Phòng Gỗ Mộc Mạc',
                'Phòng Gỗ Rustic',
                'Phòng Loft Cửa Sổ Lớn',
                'Phòng Dorm 4 Giường Tập Thể',
                'Phòng Vintage Hoài Cổ',
                'Phòng Vintage Nhỏ Xinh'
            ],
        ];

        $roomCount = 0;
        $roomsData = [];

        foreach ($properties as $property) {
            $numRooms = rand(3, 6);
            $slug = $property->slug;
            $possibleTitles = $typeRoomTitles[$slug] ?? ['Phòng Cao Cấp', 'Phòng Tiêu Chuẩn', 'Phòng Sang Trọng'];
            
            // Shuffle and pick unique titles for rooms within this property
            shuffle($possibleTitles);
            $floorSequences = [];

            for ($r = 0; $r < $numRooms; $r++) {
                $roomCount++;
                $title = $possibleTitles[$r % count($possibleTitles)];
                $description = $faker->randomElement($descriptions);
                
                // Enforce strict logical specifications based on the 4 property types slugs
                if ($slug === 'khach-san-hotel') {
                    // Hotels: 1 bedroom, double/twin beds. Suites can have 1 or 2 beds.
                    $isSuite = (strpos($title, 'Suite') !== false || strpos($title, 'Executive') !== false);
                    $bedroomsCount = 1; // Standard hotel rooms only have 1 bedroom space
                    $bedsCount = $isSuite ? 2 : rand(1, 2);
                    $roomType = $isSuite ? 2 : 1; 
                    $people = $bedsCount * rand(1, 2);
                    $area = $faker->randomFloat(2, 20, 50); // Hotels are 20m2 - 50m2
                } elseif ($slug === 'nha-nghi-guesthouse') {
                    // Guesthouses: Strictly 1 bedroom, 1 or 2 beds.
                    $bedroomsCount = 1;
                    $bedsCount = rand(1, 2);
                    $roomType = 1;
                    $people = $bedsCount * rand(1, 2);
                    $area = $faker->randomFloat(2, 15, 30); // Guesthouses are 15m2 - 30m2
                } elseif ($slug === 'homestay-co-chia-phong') {
                    // Homestays: Each room/unit has 1 bedroom. Can be a Dorm room.
                    $bedroomsCount = 1;
                    $isDorm = (strpos($title, 'Dorm') !== false);
                    $bedsCount = $isDorm ? 4 : rand(1, 2);
                    $roomType = $isDorm ? 3 : 1;
                    $people = $isDorm ? $bedsCount : rand(1, 2);
                    $area = $isDorm ? $faker->randomFloat(2, 30, 45) : $faker->randomFloat(2, 18, 35); // Dorms are larger
                } else {
                    // Serviced apartments (can-ho-dich-vu-theo-phong): 1 to 3 bedrooms
                    $roomType = $faker->numberBetween(1, 3);
                    $bedroomsCount = match ($roomType) {
                        1 => 1,
                        2 => 2,
                        3 => 3,
                        default => 1,
                    };
                    $bedsCount = match ($roomType) {
                        1 => rand(1, 2),
                        2 => rand(2, 4),
                        3 => rand(3, 6),
                        default => 1,
                    };
                    $people = match ($roomType) {
                        1 => rand(1, 2),
                        2 => rand(2, 4),
                        3 => rand(4, 8),
                        default => 2,
                    };
                    
                    // Realistic areas for apartments:
                    $area = match ($bedroomsCount) {
                        1 => $faker->randomFloat(2, 30, 50),  // 1 bedroom: 30 - 50 m2
                        2 => $faker->randomFloat(2, 55, 80),  // 2 bedrooms: 55 - 80 m2
                        3 => $faker->randomFloat(2, 85, 120), // 3 bedrooms: 85 - 120 m2
                        default => $faker->randomFloat(2, 30, 50),
                    };
                }

                $floor_number = $faker->numberBetween(1, 10);
                if (!isset($floorSequences[$floor_number])) {
                    $floorSequences[$floor_number] = 1;
                } else {
                    $floorSequences[$floor_number]++;
                }
                $room_number = (string)($floor_number * 100 + $floorSequences[$floor_number]);

                $roomsData[] = [
                    'property_id' => $property->id,
                    'title' => $title,
                    'room_number' => $room_number,
                    'deposit' => (float) (round($faker->numberBetween(500000, 10000000) / 50000) * 50000),
                    'area' => $area,
                    'floor_number' => $floor_number,
                    'people' => $people,
                    'bedrooms_count' => $bedroomsCount,
                    'beds_count' => $bedsCount,
                    'room_type' => $roomType, 
                    'status' => $faker->boolean(80), 
                    'description' => $description,
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => Carbon::now()->subDays(rand(1, 50)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 40)),
                ];
            }
        }

        collect($roomsData)->chunk(100)->each(function ($chunk) {
            DB::table('rooms')->insert($chunk->toArray());
        });
    }
}

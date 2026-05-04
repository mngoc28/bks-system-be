<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PartnerQaDataSeeder extends Seeder
{
    public function run(): void
    {
        $partnerId = 2;

        $this->cleanupPreviousQaData($partnerId);

        $partner = DB::table('users')
            ->where('id', $partnerId)
            ->where('role', 'partner')
            ->first();

        if (!$partner) {
            $this->command->warn('PartnerQaDataSeeder: user id=2 (role partner) not found, skipping.');
            return;
        }

        $provinceId = (int) (DB::table('partner_info')->where('user_id', $partnerId)->value('province_id')
            ?? DB::table('provinces')->value('id'));

        $wardId = (int) (DB::table('partner_info')->where('user_id', $partnerId)->value('ward_id')
            ?? DB::table('wards')->where('province_id', $provinceId)->value('id')
            ?? DB::table('wards')->value('id'));

        $propertyTypes = DB::table('property_types')
            ->select('id', 'name', 'slug')
            ->where('is_active', true)
            ->whereIn('slug', [
                'khach-san-hotel',
                'nha-nghi-guesthouse',
                'can-ho-dich-vu-theo-phong',
                'homestay-co-chia-phong',
            ])
            ->orderBy('id')
            ->get();

        if ($propertyTypes->isEmpty()) {
            $this->command->warn('PartnerQaDataSeeder: property_types is empty, skipping.');
            return;
        }

        $userIds = DB::table('users')
            ->where('role', 'user')
            ->pluck('id')
            ->values()
            ->all();

        if (empty($userIds)) {
            $this->command->warn('PartnerQaDataSeeder: users with role=user not found, skipping bookings.');
        }

        $pricePackageIds = DB::table('price_packages')->pluck('id')->values()->all();
        $amenityIds = DB::table('amenities')->pluck('id')->values()->all();
        $serviceIds = DB::table('services')->pluck('id')->values()->all();

        $buildingMeta = [];
        $roomIds = [];
        $roomTypeSlugByRoomId = [];
        $roomPriceIdsByRoom = [];

        $typeProfiles = [
            'khach-san-hotel' => ['rent_category' => 2, 'floors' => 18, 'units' => 220, 'area' => 8800, 'room_count' => 10, 'label' => 'Hotel', 'property_count' => 2],
            'nha-nghi-guesthouse' => ['rent_category' => 2, 'floors' => 5, 'units' => 45, 'area' => 1400, 'room_count' => 6, 'label' => 'Guesthouse', 'property_count' => 2],
            'can-ho-dich-vu-theo-phong' => ['rent_category' => 2, 'floors' => 14, 'units' => 130, 'area' => 5200, 'room_count' => 7, 'label' => 'Serviced Apt', 'property_count' => 2],
            'homestay-co-chia-phong' => ['rent_category' => 2, 'floors' => 3, 'units' => 24, 'area' => 900, 'room_count' => 6, 'label' => 'Homestay', 'property_count' => 2],
        ];

        $propertySerial = 1;
        foreach ($propertyTypes as $idx => $type) {
            $profile = $typeProfiles[$type->slug] ?? ['rent_category' => 2, 'floors' => 10, 'units' => 80, 'area' => 4000, 'room_count' => 5, 'label' => 'Property', 'property_count' => 1];
            $propertyCount = (int) ($profile['property_count'] ?? 1);

            for ($p = 1; $p <= $propertyCount; $p++) {
                $buildingId = (int) DB::table('buildings')->insertGetId([
                    'user_id' => $partnerId,
                    'province_id' => $provinceId,
                    'ward_id' => $wardId,
                    'name' => sprintf('QA %s Property %02d-%d', $profile['label'], $propertySerial, $p),
                    'address_detail' => sprintf('%d QA Street, Partner Test Ward, Partner Test City', 100 + $propertySerial + $p),
                    'number_of_floors' => $profile['floors'],
                    'number_of_units' => $profile['units'],
                    'year_built' => 2016 + (($idx + $p) % 9),
                    'property_type_id' => (int) $type->id,
                    'rent_category' => $profile['rent_category'],
                    'area' => (float) $profile['area'],
                    'description' => sprintf('QA property for type [%s] to validate type-based UI behavior in partner portal.', $type->name),
                    'created_by' => $partnerId,
                    'updated_by' => $partnerId,
                    'created_at' => Carbon::now()->subDays(20 - $idx),
                    'updated_at' => Carbon::now()->subDays(10 - $idx),
                ]);

                $buildingMeta[$buildingId] = [
                    'room_count' => $profile['room_count'],
                    'label' => $profile['label'],
                    'slug' => (string) $type->slug,
                ];
            }

            $propertySerial++;
        }

        $roomCounter = 1;
        $buildingIndex = 0;
        foreach ($buildingMeta as $buildingId => $meta) {
            for ($i = 1; $i <= (int) $meta['room_count']; $i++) {
                $baseArea = 18 + ($i * 2.5);
                $basePeople = $i % 3 === 0 ? 4 : 2;
                $baseRoomType = ($i % 3) + 1;
                $baseStatus = $i % 4 !== 0;
                $baseFloor = min($i + 1, 10);
                $baseDeposit = 1500000 + ($i * 150000);

                if ($meta['slug'] === 'khach-san-hotel') {
                    $basePeople = min($basePeople, 3);
                    $baseRoomType = 2;
                }
                if ($meta['slug'] === 'can-ho-dich-vu-theo-phong') {
                    $baseArea += 12;
                    $baseDeposit += 1200000;
                    $baseRoomType = 3;
                }
                if ($meta['slug'] === 'homestay-co-chia-phong') {
                    $baseFloor = min($i + 1, 3);
                    $basePeople = max($basePeople, 3);
                }
                if ($meta['slug'] === 'nha-nghi-guesthouse') {
                    $baseArea = min($baseArea, 28);
                    $baseDeposit = max(500000, $baseDeposit - 500000);
                }

                $roomId = (int) DB::table('rooms')->insertGetId([
                    'building_id' => $buildingId,
                    'title' => sprintf('QA %s Room %02d-%02d', $meta['label'], $buildingIndex + 1, $i),
                    'room_number' => sprintf('QA-%d%02d', $buildingIndex + 1, $i),
                    'deposit' => $baseDeposit,
                    'area' => $baseArea,
                    'floor_number' => $baseFloor,
                    'people' => $basePeople,
                    'room_type' => $baseRoomType,
                    'status' => $baseStatus,
                    'description' => sprintf('QA seeded room for type [%s] regression and edge-case verification.', $meta['slug']),
                    'created_by' => $partnerId,
                    'updated_by' => $partnerId,
                    'created_at' => Carbon::now()->subDays(18 - $i),
                    'updated_at' => Carbon::now()->subDays(8 - $i),
                ]);

                $roomIds[] = $roomId;
                $roomTypeSlugByRoomId[$roomId] = (string) $meta['slug'];

                $selectedPackageIds = array_slice($pricePackageIds, 0, min(2, count($pricePackageIds)));
                if (empty($selectedPackageIds)) {
                    continue;
                }

                foreach ($selectedPackageIds as $j => $packageId) {
                    $dayPrice = 280000 + ($roomCounter * 12000) + ($j * 25000);
                    $monthPrice = $dayPrice * 26;

                    $dayPriceId = (int) DB::table('room_prices')->insertGetId([
                        'room_id' => $roomId,
                        'price_package_id' => $packageId,
                        'unit' => 'day',
                        'price' => $dayPrice,
                        'created_by' => $partnerId,
                        'updated_by' => $partnerId,
                        'created_at' => Carbon::now()->subDays(16),
                        'updated_at' => Carbon::now()->subDays(5),
                    ]);

                    DB::table('room_prices')->insert([
                        'room_id' => $roomId,
                        'price_package_id' => $packageId,
                        'unit' => 'month',
                        'price' => $monthPrice,
                        'created_by' => $partnerId,
                        'updated_by' => $partnerId,
                        'created_at' => Carbon::now()->subDays(16),
                        'updated_at' => Carbon::now()->subDays(5),
                    ]);

                    $roomPriceIdsByRoom[$roomId][] = $dayPriceId;
                }

                if (!empty($amenityIds)) {
                    $seedAmenityIds = array_slice($amenityIds, 0, min(5, count($amenityIds)));
                    foreach ($seedAmenityIds as $amenityId) {
                        DB::table('room_amenities')->insert([
                            'room_id' => $roomId,
                            'amenity_id' => $amenityId,
                            'created_by' => $partnerId,
                            'updated_by' => $partnerId,
                            'created_at' => Carbon::now()->subDays(14),
                            'updated_at' => Carbon::now()->subDays(4),
                        ]);
                    }
                }

                if (!empty($serviceIds)) {
                    $seedServiceIds = array_slice($serviceIds, 0, min(4, count($serviceIds)));
                    foreach ($seedServiceIds as $serviceId) {
                        DB::table('room_services')->insert([
                            'room_id' => $roomId,
                            'service_id' => $serviceId,
                            'created_by' => $partnerId,
                            'updated_by' => $partnerId,
                            'created_at' => Carbon::now()->subDays(14),
                            'updated_at' => Carbon::now()->subDays(4),
                        ]);
                    }
                }

                $roomCounter++;
            }
            $buildingIndex++;
        }

        if (!empty($userIds)) {
            $bookingIdx = 0;

            foreach ($roomIds as $roomId) {
                $priceIds = $roomPriceIdsByRoom[$roomId] ?? [];
                if (empty($priceIds)) {
                    continue;
                }

                $slug = $roomTypeSlugByRoomId[$roomId] ?? 'default';
                $statusPattern = $this->getStatusPatternByPropertyType($slug);
                $bookingCount = $this->getBookingCountByPropertyType($slug);

                for ($k = 0; $k < $bookingCount; $k++) {
                    $status = $statusPattern[$k % count($statusPattern)];
                    [$startDate, $endDate, $temporalTag] = $this->getBookingDateWindowByPropertyType($slug, $k, $bookingIdx);

                    DB::table('bookings')->insert([
                        'user_id' => $userIds[$bookingIdx % count($userIds)],
                        'room_id' => $roomId,
                        'price_id' => $priceIds[$bookingIdx % count($priceIds)],
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => $status,
                        'note' => sprintf('QA seed booking for partner dashboard and booking management. [temporal=%s]', $temporalTag),
                        'created_by' => $partnerId,
                        'updated_by' => $partnerId,
                        'created_at' => Carbon::now()->subDays(9 - ($bookingIdx % 5)),
                        'updated_at' => Carbon::now()->subDays(3),
                    ]);

                    $bookingIdx++;
                }
            }
        }

        $maintenanceStates = ['planned', 'in_progress', 'completed', 'cancelled'];
        foreach (array_slice($roomIds, 0, min(12, count($roomIds))) as $idx => $roomId) {
            $buildingId = (int) DB::table('rooms')->where('id', $roomId)->value('building_id');

            DB::table('room_maintenances')->insert([
                'room_id' => $roomId,
                'property_id' => $buildingId,
                'title' => sprintf('QA Maintenance %02d', $idx + 1),
                'description' => 'QA seeded maintenance task for list/filter/status verification.',
                'maintenance_type' => $idx % 2 === 0 ? 'emergency' : 'scheduled',
                'start_time' => Carbon::now()->subDays(6 - ($idx % 4))->setHour(9),
                'end_time' => Carbon::now()->addDays(2 + ($idx % 3))->setHour(14),
                'status' => $maintenanceStates[$idx % count($maintenanceStates)],
                'created_by' => $partnerId,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(2),
            ]);
        }

        for ($i = 1; $i <= 12; $i++) {
            DB::table('news')->insert([
                'user_id' => $partnerId,
                'title' => sprintf('QA Partner Update %02d', $i),
                'slug' => Str::slug(sprintf('qa-partner-update-%02d-%s', $i, Carbon::now()->timestamp)),
                'summary' => 'Bai viet duoc seed cho QA de test danh sach, trang thai va thao tac quan ly tin tuc.',
                'content' => 'Noi dung seed cho tai khoan partner id=2, bao phu cac kich ban draft/published va list paging.',
                'status' => $i % 3 === 0 ? 0 : 1,
                'published_at' => $i % 3 === 0 ? null : Carbon::now()->subDays($i),
                'image_url' => null,
                'id_image_cloudinary' => null,
                'created_by' => $partnerId,
                'updated_by' => $partnerId,
                'created_at' => Carbon::now()->subDays(12 - $i),
                'updated_at' => Carbon::now()->subDays(6 - ($i % 4)),
            ]);
        }

        $this->command->info('PartnerQaDataSeeder: generated rich QA data for partner user id=2.');
    }

    /**
     * Booking status edge-case by property type.
     * 0=pending, 1=confirmed, 2=cancelled, 3=completed
     */
    private function getStatusPatternByPropertyType(string $slug): array
    {
        return match ($slug) {
            'khach-san-hotel' => [0, 0, 1, 0, 3],
            'nha-nghi-guesthouse' => [1, 0, 3, 1],
            'can-ho-dich-vu-theo-phong' => [1, 1, 3, 0, 1],
            'homestay-co-chia-phong' => [0, 1, 3, 2, 0],
            default => [0, 1, 3, 2],
        };
    }

    /**
     * Increase sample size for types where filters are most important.
     */
    private function getBookingCountByPropertyType(string $slug): int
    {
        return match ($slug) {
            'khach-san-hotel' => 4,
            'can-ho-dich-vu-theo-phong', 'homestay-co-chia-phong' => 3,
            default => 2,
        };
    }

    /**
     * Return booking date windows to cover temporal edge-cases.
     *
     * @return array{0:string,1:string,2:string}
     */
    private function getBookingDateWindowByPropertyType(string $slug, int $slot, int $seedIndex): array
    {
        $today = Carbon::today();

        if ($slug === 'khach-san-hotel') {
            return match ($slot % 4) {
                0 => [$today->copy()->subDays(7)->toDateString(), $today->copy()->subDays(1)->toDateString(), 'overdue'],
                1 => [$today->copy()->toDateString(), $today->copy()->addDays(2)->toDateString(), 'checkin_today'],
                2 => [$today->copy()->addDay()->toDateString(), $today->copy()->addDays(4)->toDateString(), 'checkin_tomorrow'],
                default => [$today->copy()->addDay()->toDateString(), $today->copy()->addDays(3)->toDateString(), 'overlap_window'],
            };
        }

        if ($slug === 'nha-nghi-guesthouse') {
            return match ($slot % 3) {
                0 => [$today->copy()->subDays(25)->toDateString(), $today->copy()->subDays(4)->toDateString(), 'short_stay_past'],
                1 => [$today->copy()->subDays(20)->toDateString(), $today->copy()->subDays(2)->toDateString(), 'recently_completed'],
                default => [$today->copy()->addDays(2)->toDateString(), $today->copy()->addDays(14)->toDateString(), 'upcoming'],
            };
        }

        if ($slug === 'can-ho-dich-vu-theo-phong') {
            return match ($slot % 2) {
                0 => [$today->copy()->subDays(8)->toDateString(), $today->copy()->addDays(4)->toDateString(), 'long_running_stay'],
                default => [$today->copy()->addDays(4)->toDateString(), $today->copy()->addDays(18)->toDateString(), 'future_serviced_stay'],
            };
        }

        $start = $today->copy()->subDays(4 - ($seedIndex % 6));
        $end = $start->copy()->addDays(6 + ($slot % 4));
        return [$start->toDateString(), $end->toDateString(), 'mixed_window'];
    }

    private function cleanupPreviousQaData(int $partnerId): void
    {
        $qaBuildingIds = DB::table('buildings')
            ->where('user_id', $partnerId)
            ->where('name', 'like', 'QA %')
            ->pluck('id')
            ->values()
            ->all();

        if (empty($qaBuildingIds)) {
            DB::table('news')
                ->where('user_id', $partnerId)
                ->where('title', 'like', 'QA %')
                ->delete();
            return;
        }

        $qaRoomIds = DB::table('rooms')
            ->whereIn('building_id', $qaBuildingIds)
            ->pluck('id')
            ->values()
            ->all();

        if (!empty($qaRoomIds)) {
            DB::table('bookings')->whereIn('room_id', $qaRoomIds)->delete();
            DB::table('room_amenities')->whereIn('room_id', $qaRoomIds)->delete();
            DB::table('room_services')->whereIn('room_id', $qaRoomIds)->delete();
            DB::table('room_prices')->whereIn('room_id', $qaRoomIds)->delete();
            DB::table('room_maintenances')->whereIn('room_id', $qaRoomIds)->delete();
            DB::table('rooms')->whereIn('id', $qaRoomIds)->delete();
        }

        DB::table('room_maintenances')->whereIn('property_id', $qaBuildingIds)->delete();
        DB::table('buildings')->whereIn('id', $qaBuildingIds)->delete();
        DB::table('news')
            ->where('user_id', $partnerId)
            ->where('title', 'like', 'QA %')
            ->delete();
    }
}

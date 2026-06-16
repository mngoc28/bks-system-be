<?php

declare(strict_types=1);

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class HospitalityRulesUpdateSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('vi_VN');

        $propertyIds = DB::table('properties')->pluck('id')->toArray();
        $total = count($propertyIds);

        if ($total === 0) {
            $this->command?->warn('Không có properties nào để seed. Chạy PropertiesTableSeeder trước.');

            return;
        }

        $this->command?->info("Đang cập nhật hospitality rules cho {$total} properties...");

        DB::table('properties')->update([
            'pet_policy'             => 'not_allowed',
            'pet_policy_note'        => null,
            'smoking_allowed'        => false,
            'parties_allowed'        => false,
            'quiet_hours_start'      => '22:00:00',
            'quiet_hours_end'        => '06:00:00',
            'standard_checkin_start' => '14:00:00',
            'standard_checkout_end'  => '12:00:00',
            'checkin_method'         => 'meet_host',
            'has_elevator'           => false,
            'has_step_free_access'   => false,
            'is_ground_floor'        => false,
        ]);

        $smokingIds = $faker->randomElements($propertyIds, max(1, (int) ($total * 0.15)));
        DB::table('properties')
            ->whereIn('id', $smokingIds)
            ->update(['smoking_allowed' => true]);

        $partiesIds = $faker->randomElements($propertyIds, max(1, (int) ($total * 0.10)));
        DB::table('properties')
            ->whereIn('id', $partiesIds)
            ->update(['parties_allowed' => true]);

        $petAllowedIds = $faker->randomElements($propertyIds, max(1, (int) ($total * 0.20)));
        DB::table('properties')
            ->whereIn('id', $petAllowedIds)
            ->update(['pet_policy' => 'allowed']);

        $remaining = array_diff($propertyIds, $petAllowedIds);
        $petConditionalIds = $faker->randomElements(array_values($remaining), max(1, (int) ($total * 0.15)));
        DB::table('properties')
            ->whereIn('id', $petConditionalIds)
            ->update([
                'pet_policy'      => 'conditional',
                'pet_policy_note' => $faker->randomElement([
                    'Chỉ chấp nhận thú cưng dưới 5kg',
                    'Phải thông báo trước khi đặt phòng',
                    'Yêu cầu đặt cọc thêm 500.000đ cho thú cưng',
                    'Chỉ nhận chó, mèo đã tiêm phòng đầy đủ',
                    'Không được để thú cưng lên giường/sofa',
                ]),
            ]);

        $shuffled = $propertyIds;
        shuffle($shuffled);
        $chunks = array_chunk($shuffled, max(1, (int) ($total / 4)));

        if (isset($chunks[0])) {
            DB::table('properties')->whereIn('id', $chunks[0])
                ->update(['checkin_method' => 'smart_lock']);
        }
        if (isset($chunks[1])) {
            DB::table('properties')->whereIn('id', $chunks[1])
                ->update(['checkin_method' => 'lockbox']);
        }
        if (isset($chunks[2])) {
            DB::table('properties')->whereIn('id', $chunks[2])
                ->update(['checkin_method' => 'reception_24h']);
        }

        $earlyCheckin = $faker->randomElements($propertyIds, max(1, (int) ($total * 0.30)));
        DB::table('properties')->whereIn('id', $earlyCheckin)
            ->update(['standard_checkin_start' => '13:00:00']);

        $lateCheckin = $faker->randomElements(array_diff($propertyIds, $earlyCheckin), max(1, (int) ($total * 0.10)));
        DB::table('properties')->whereIn('id', $lateCheckin)
            ->update(['standard_checkin_start' => '15:00:00']);

        $earlyCheckout = $faker->randomElements($propertyIds, max(1, (int) ($total * 0.20)));
        DB::table('properties')->whereIn('id', $earlyCheckout)
            ->update(['standard_checkout_end' => '11:00:00']);

        $noQuietHours = $faker->randomElements($propertyIds, max(1, (int) ($total * 0.25)));
        DB::table('properties')
            ->whereIn('id', $noQuietHours)
            ->update(['quiet_hours_start' => null, 'quiet_hours_end' => null]);

        $elevatorIds = $faker->randomElements($propertyIds, max(1, (int) ($total * 0.40)));
        DB::table('properties')->whereIn('id', $elevatorIds)
            ->update(['has_elevator' => true]);

        $groundIds = $faker->randomElements(array_diff($propertyIds, $elevatorIds), max(1, (int) ($total * 0.10)));
        DB::table('properties')->whereIn('id', $groundIds)
            ->update(['is_ground_floor' => true]);

        $roomIds = DB::table('rooms')->pluck('id')->toArray();
        $totalRooms = count($roomIds);

        $extraFeeIds = $faker->randomElements($roomIds, max(1, (int) ($totalRooms * 0.40)));
        foreach ($extraFeeIds as $roomId) {
            DB::table('rooms')->where('id', $roomId)->update([
                'base_people'      => $faker->randomElement([1, 2]),
                'extra_people_fee' => $faker->randomElement([50000, 100000, 150000, 200000]),
            ]);
        }

        $this->command?->info('✅ HospitalityRulesUpdateSeeder hoàn tất.');
    }
}

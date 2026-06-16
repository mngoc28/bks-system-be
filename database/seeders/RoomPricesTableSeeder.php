<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * @see ../../../docs/developer/room-price-seeder-formulas.md Công thức seed và biên giá theo loại hình
 */
final class RoomPricesTableSeeder extends Seeder
{
    /**
     * Mỗi phòng chỉ gắn một price_package (gói chuẩn) để UI EndUser hiển thị tối đa
     * một thẻ "Thuê ngắn hạn" và một thẻ "Thuê dài hạn" — tránh nhiều giá trùng nhãn.
     *
     * @var array<string, array{units: list<string>, group: string}>
     */
    private const PRICING_BY_PROPERTY_SLUG = [
        'khach-san-hotel' => [
            'units' => ['night'],
            'group' => 'short_term',
        ],
        'nha-nghi-guesthouse' => [
            'units' => ['night'],
            'group' => 'short_term',
        ],
        'homestay-co-chia-phong' => [
            'units' => ['night', 'month'],
            'group' => 'flexible',
        ],
        'can-ho-dich-vu-theo-phong' => [
            'units' => ['month', 'night'],
            'group' => 'long_term',
        ],
    ];

    /** Gói "medium" — một gói giá chuẩn mỗi phòng (khớp PricePackagesTableSeeder id=3). */
    private const DEFAULT_PACKAGE_ID = 3;

    private const PACKAGE_MULTIPLIERS = [
        1 => 0.7,
        2 => 0.85,
        3 => 1.0,
        4 => 1.3,
    ];

    /**
     * Biên giá/đêm theo loại hình — tránh dùng một trần 5M chung khiến mọi thẻ "Giá từ" trùng nhau.
     *
     * @var array<string, array{per_sqm_min: int, per_sqm_max: int, night_min: int, night_max: int}>
     */
    private const NIGHT_BOUNDS_BY_SLUG = [
        'khach-san-hotel' => [
            'per_sqm_min' => 55_000,
            'per_sqm_max' => 95_000,
            'night_min' => 650_000,
            'night_max' => 3_800_000,
        ],
        'nha-nghi-guesthouse' => [
            'per_sqm_min' => 35_000,
            'per_sqm_max' => 65_000,
            'night_min' => 220_000,
            'night_max' => 1_600_000,
        ],
        'homestay-co-chia-phong' => [
            'per_sqm_min' => 40_000,
            'per_sqm_max' => 75_000,
            'night_min' => 300_000,
            'night_max' => 2_400_000,
        ],
    ];

    private const HOMESTAY_MONTH_BOUNDS = ['min' => 5_500_000, 'max' => 28_000_000];

    private const MONTHLY_APARTMENT_BOUNDS = ['min' => 7_000_000, 'max' => 48_000_000];

    private const APARTMENT_SHORT_TERM_DAY_RATE_PERCENT = 30;

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

        if ($adminPartnerIds === []) {
            $adminPartnerIds = [1];
        }

        $roomIds = DB::table('rooms')->pluck('id')->toArray();
        $packageIds = DB::table('price_packages')->pluck('id')->toArray();

        if ($roomIds === [] || $packageIds === []) {
            $this->command->warn('No rooms or price packages found. Please run RoomsTableSeeder and PricePackagesTableSeeder first.');

            return;
        }

        $roomPropertySlugs = DB::table('rooms')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->join('property_types', 'properties.property_type_id', '=', 'property_types.id')
            ->select('rooms.id as room_id', 'property_types.slug as property_type_slug')
            ->get()
            ->keyBy('room_id');

        $rows = [];

        foreach ($roomIds as $roomId) {
            $room = DB::table('rooms')->where('id', $roomId)->first();
            $area = $room instanceof stdClass ? (float) $room->area : 25.0;

            $propertySlug = (string) ($roomPropertySlugs->get($roomId)?->property_type_slug ?? '');
            $config = self::PRICING_BY_PROPERTY_SLUG[$propertySlug] ?? null;

            if ($config === null) {
                $this->command->warn("Unknown property type slug \"{$propertySlug}\" for room {$roomId}; using day-only short_term fallback.");
                $config = [
                    'units' => ['night'],
                    'group' => 'short_term',
                ];
            }

            $rows = array_merge(
                $rows,
                $this->buildPricesForRoom(
                    (int) $roomId,
                    $propertySlug,
                    $area,
                    array_map('intval', $packageIds),
                    $config,
                    $faker,
                    $adminPartnerIds,
                ),
            );
        }

        collect($rows)->chunk(100)->each(function ($chunk): void {
            DB::table('room_prices')->insert($chunk->toArray());
        });
    }

    /**
     * @param array{units: list<string>, group: string} $config
     * @param list<int> $packageIds
     * @param list<int> $adminPartnerIds
     *
     * @return list<array<string, mixed>>
     */
    private function buildPricesForRoom(
        int $roomId,
        string $propertySlug,
        float $area,
        array $packageIds,
        array $config,
        Generator $faker,
        array $adminPartnerIds,
    ): array {
        $rows = [];
        $nightRate = $this->resolveNightRate($propertySlug, $area, $faker);
        $apartmentMonthlyBase = $propertySlug === 'can-ho-dich-vu-theo-phong'
            ? $this->resolveMonthlyApartmentRate($area, $faker)
            : null;

        $packageId = $this->resolveDefaultPackageId($packageIds);
        $packageMultiplier = $this->packageMultiplier($packageId);
        $group = $config['group'];

        if ($group === 'short_term') {
            $rows[] = $this->makeRow(
                $roomId,
                $packageId,
                'night',
                $propertySlug,
                $nightRate,
                $packageMultiplier,
                $apartmentMonthlyBase,
                $faker,
                $adminPartnerIds,
            );

            return $rows;
        }

        if ($group === 'flexible') {
            $rows[] = $this->makeRow(
                $roomId,
                $packageId,
                'night',
                $propertySlug,
                $nightRate,
                $packageMultiplier,
                $apartmentMonthlyBase,
                $faker,
                $adminPartnerIds,
            );
            $rows[] = $this->makeRow(
                $roomId,
                $packageId,
                'month',
                $propertySlug,
                $nightRate,
                $packageMultiplier,
                $apartmentMonthlyBase,
                $faker,
                $adminPartnerIds,
            );

            return $rows;
        }

        $rows[] = $this->makeRow(
            $roomId,
            $packageId,
            'month',
            $propertySlug,
            $nightRate,
            $packageMultiplier,
            $apartmentMonthlyBase,
            $faker,
            $adminPartnerIds,
        );

        if (random_int(1, 100) <= self::APARTMENT_SHORT_TERM_DAY_RATE_PERCENT) {
            $rows[] = $this->makeRow(
                $roomId,
                $packageId,
                'night',
                $propertySlug,
                $nightRate,
                $packageMultiplier,
                $apartmentMonthlyBase,
                $faker,
                $adminPartnerIds,
            );
        }

        return $rows;
    }

    /**
     * @param list<int> $packageIds
     */
    private function resolveDefaultPackageId(array $packageIds): int
    {
        if (in_array(self::DEFAULT_PACKAGE_ID, $packageIds, true)) {
            return self::DEFAULT_PACKAGE_ID;
        }

        return $packageIds[0];
    }

    /**
     * @param list<int> $adminPartnerIds
     *
     * @return array<string, mixed>
     */
    private function makeRow(
        int $roomId,
        int $packageId,
        string $unit,
        string $propertySlug,
        float $nightRate,
        float $packageMultiplier,
        ?float $apartmentMonthlyBase,
        Generator $faker,
        array $adminPartnerIds,
    ): array {
        $price = $this->resolvePriceForUnit(
            $propertySlug,
            $unit,
            $nightRate,
            $packageMultiplier,
            $apartmentMonthlyBase,
            $faker,
        );

        ['deposit_amount' => $depositAmount, 'minimum_stay' => $minimumStay] = $this->resolveDepositAndMinimumStay(
            $propertySlug,
            $unit,
            $price,
        );

        return [
            'room_id' => $roomId,
            'price_package_id' => $packageId,
            'unit' => $unit,
            'price' => $price,
            'deposit_amount' => $depositAmount,
            'minimum_stay' => $minimumStay,
            'created_by' => $faker->randomElement($adminPartnerIds),
            'updated_by' => $faker->randomElement($adminPartnerIds),
            'created_at' => Carbon::now()->subDays(random_int(1, 40)),
            'updated_at' => Carbon::now()->subDays(random_int(1, 40)),
        ];
    }

    private function resolveNightRate(string $propertySlug, float $area, Generator $faker): float
    {
        $bounds = self::NIGHT_BOUNDS_BY_SLUG[$propertySlug] ?? self::NIGHT_BOUNDS_BY_SLUG['khach-san-hotel'];

        $perSqm = $faker->randomFloat(2, (float) $bounds['per_sqm_min'], (float) $bounds['per_sqm_max']);
        $rate = $area * $perSqm * $faker->randomFloat(2, 0.88, 1.12);

        return $this->clamp($rate, (float) $bounds['night_min'], (float) $bounds['night_max']);
    }

    private function resolveMonthlyApartmentRate(float $area, Generator $faker): float
    {
        $perSqm = $faker->randomFloat(2, 250_000, 450_000);
        $rate = $area * $perSqm * $faker->randomFloat(2, 0.9, 1.1);

        return $this->clamp(
            $rate,
            (float) self::MONTHLY_APARTMENT_BOUNDS['min'],
            (float) self::MONTHLY_APARTMENT_BOUNDS['max'],
        );
    }

    private function resolveHomestayMonthlyRate(float $nightRate, Generator $faker): float
    {
        $rate = $nightRate * $faker->randomFloat(2, 20, 24);

        return $this->clamp(
            $rate,
            (float) self::HOMESTAY_MONTH_BOUNDS['min'],
            (float) self::HOMESTAY_MONTH_BOUNDS['max'],
        );
    }

    private function resolvePriceForUnit(
        string $propertySlug,
        string $unit,
        float $nightRate,
        float $packageMultiplier,
        ?float $apartmentMonthlyBase,
        Generator $faker,
    ): float {
        if ($unit === 'month') {
            if ($propertySlug === 'can-ho-dich-vu-theo-phong' && $apartmentMonthlyBase !== null) {
                return $this->applyPackageMultiplier($apartmentMonthlyBase, $packageMultiplier);
            }

            if ($propertySlug === 'homestay-co-chia-phong') {
                return $this->applyPackageMultiplier(
                    $this->resolveHomestayMonthlyRate($nightRate, $faker),
                    $packageMultiplier,
                );
            }

            return $this->applyPackageMultiplier($nightRate * 25, $packageMultiplier);
        }

        if ($propertySlug === 'can-ho-dich-vu-theo-phong' && $apartmentMonthlyBase !== null) {
            return $this->applyPackageMultiplier($apartmentMonthlyBase / 30, $packageMultiplier);
        }

        return $this->applyPackageMultiplier($nightRate, $packageMultiplier);
    }

    /**
     * @return array{deposit_amount: ?float, minimum_stay: int}
     */
    private function resolveDepositAndMinimumStay(string $propertySlug, string $unit, float $price): array
    {
        if ($unit === 'month' && in_array($propertySlug, ['homestay-co-chia-phong', 'can-ho-dich-vu-theo-phong'], true)) {
            return [
                'deposit_amount' => $price,
                'minimum_stay' => 1,
            ];
        }

        return [
            'deposit_amount' => null,
            'minimum_stay' => 1,
        ];
    }

    private function applyPackageMultiplier(float $base, float $multiplier): float
    {
        $calculated = $base * $multiplier;
        // Round to the nearest 10,000 VND for professional hospitality pricing
        return (float) (round($calculated / 10000) * 10000);
    }

    private function packageMultiplier(int $packageId): float
    {
        return self::PACKAGE_MULTIPLIERS[$packageId] ?? 1.0;
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}

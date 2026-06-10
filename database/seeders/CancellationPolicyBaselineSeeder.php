<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CancellationPolicyBaselineSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('cancellation_policy_versions')->updateOrInsert(
            ['version' => '2026-baseline-v1'],
            [
                'effective_from' => '2026-01-01',
                'effective_to'   => null,
                'note'           => 'BCP baseline (Phase B1). Tier % placeholder BA/research (Phase B5).',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        );

        $version = '2026-baseline-v1';
        DB::table('cancellation_policy_tiers')->where('version', $version)->delete();

        $tierRows = [
            ['stay_kind' => 'short', 'hours_before_checkin_min' => 168, 'hours_before_checkin_max' => null, 'fee_percent' => 0.00, 'refund_percent' => 100.00],
            ['stay_kind' => 'short', 'hours_before_checkin_min' => 48, 'hours_before_checkin_max' => 167, 'fee_percent' => 50.00, 'refund_percent' => 50.00],
            ['stay_kind' => 'short', 'hours_before_checkin_min' => 0, 'hours_before_checkin_max' => 47, 'fee_percent' => 100.00, 'refund_percent' => 0.00],
            ['stay_kind' => 'long', 'hours_before_checkin_min' => 720, 'hours_before_checkin_max' => null, 'fee_percent' => 0.00, 'refund_percent' => 100.00],
            ['stay_kind' => 'long', 'hours_before_checkin_min' => 168, 'hours_before_checkin_max' => 719, 'fee_percent' => 50.00, 'refund_percent' => 50.00],
            ['stay_kind' => 'long', 'hours_before_checkin_min' => 0, 'hours_before_checkin_max' => 167, 'fee_percent' => 100.00, 'refund_percent' => 0.00],
        ];

        foreach ($tierRows as $row) {
            DB::table('cancellation_policy_tiers')->insert([
                'version'                   => $version,
                'stay_kind'                 => $row['stay_kind'],
                'hours_before_checkin_min'  => $row['hours_before_checkin_min'],
                'hours_before_checkin_max'  => $row['hours_before_checkin_max'],
                'fee_percent'               => $row['fee_percent'],
                'refund_percent'            => $row['refund_percent'],
                'created_at'                => $now,
                'updated_at'                => $now,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CouponsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('coupons')->insert([
            [
                'code' => 'SUMMER50',
                'type' => 'percent',
                'value' => 50,
                'min_order_value' => 200000,
                'max_discount_value' => 500000,
                'usage_limit' => 100,
                'used_count' => 0,
                'start_date' => Carbon::parse('2025-06-01 00:00:00'),
                'end_date' => Carbon::parse('2025-08-31 23:59:59'),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'code' => 'WELCOME100K',
                'type' => 'fixed',
                'value' => 100000,
                'min_order_value' => 500000,
                'max_discount_value' => null,
                'usage_limit' => 50,
                'used_count' => 0,
                'start_date' => Carbon::parse('2025-01-01 00:00:00'),
                'end_date' => Carbon::parse('2025-12-31 23:59:59'),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}

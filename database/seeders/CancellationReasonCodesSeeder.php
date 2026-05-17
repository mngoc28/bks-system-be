<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CancellationReasonCodesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            ['change_of_plans', 'Đổi kế hoạch / lịch trình', false, 10],
            ['found_alternative', 'Đã tìm được chỗ ở khác', false, 20],
            ['pricing_concern', 'Lo ngại về giá / thanh toán', false, 30],
            ['travel_issue', 'Vấn đề di chuyển / visa', false, 40],
            ['property_mismatch', 'Thông tin phòng/khách sạn không khớp mong đợi', false, 50],
            ['other', 'Lý do khác', true, 90],
        ];

        foreach ($rows as [$code, $labelVi, $requiresNote, $sortOrder]) {
            DB::table('cancellation_reason_codes')->updateOrInsert(
                ['code' => $code],
                [
                    'label_vi'       => $labelVi,
                    'requires_note'  => $requiresNote,
                    'sort_order'     => $sortOrder,
                    'is_active'      => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ],
            );
        }
    }
}

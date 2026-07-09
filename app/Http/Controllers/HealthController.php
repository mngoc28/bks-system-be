<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class HealthController extends Controller
{
    /**
     * Lightweight liveness check — không truy vấn DB.
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'pong',
        ]);
    }

    /**
     * DB heartbeat cho cron bên thứ 3 (UptimeRobot, cron-job.org, …).
     * Chỉ chạy SELECT 1 — tối thiểu tài nguyên, đủ để MySQL/Aiven ghi nhận activity.
     */
    public function db(): JsonResponse
    {
        try {
            DB::select('SELECT 1');

            return response()->json([
                'success' => true,
                'db'      => 'ok',
                'at'      => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'db'      => 'error',
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}

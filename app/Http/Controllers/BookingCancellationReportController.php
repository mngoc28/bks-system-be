<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BookingCancellationMetricsService;
use Illuminate\Http\JsonResponse;

/**
 * B5.6: endpoint nội bộ admin — metrics SLA + pending stale (BCP).
 */
final class BookingCancellationReportController extends Controller
{
    public function __construct(
        private readonly BookingCancellationMetricsService $metricsService,
    ) {
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->metricsService->summary(),
        ]);
    }
}

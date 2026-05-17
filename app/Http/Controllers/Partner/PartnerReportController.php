<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PartnerReportController extends Controller
{
    protected ReportingService $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
    }

    /**
     * Get hospitality KPIs for the partner
     */
    public function getKPIs(Request $request): JsonResponse
    {
        $partnerId = Auth::id();
        $range = $request->query('range', 'month');

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if (!$startDate || !$endDate) {
            switch ($range) {
                case '7days':
                    $startDate = now()->subDays(6)->format('Y-m-d');
                    $endDate = now()->format('Y-m-d');
                    break;
                case '30days':
                    $startDate = now()->subDays(29)->format('Y-m-d');
                    $endDate = now()->format('Y-m-d');
                    break;
                case 'year':
                    $startDate = now()->subYear()->format('Y-m-d');
                    $endDate = now()->format('Y-m-d');
                    break;
                case 'month':
                default:
                    $startDate = now()->startOfMonth()->format('Y-m-d');
                    $endDate = now()->endOfMonth()->format('Y-m-d');
                    break;
            }
        }

        try {
            $kpis = $this->reportingService->getPartnerKPIs((int) $partnerId, $startDate, $endDate);
            return $this->successResponse($kpis, 'KPIs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to calculate KPIs', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }
}

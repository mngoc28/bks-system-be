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
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->endOfMonth()->format('Y-m-d'));

        try {
            $kpis = $this->reportingService->getPartnerKPIs((int) $partnerId, $startDate, $endDate);
            return $this->successResponse($kpis, 'KPIs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to calculate KPIs', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }
}

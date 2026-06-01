<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueReportingService;
use App\Services\SettlementService;
use App\Repositories\PartnerSettlementPeriodRepository\PartnerSettlementPeriodRepositoryInterface;
use App\Repositories\SettlementLineItemRepository\SettlementLineItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Controller dành cho Admin quản lý đối soát tài chính với đối tác.
 */
class AdminSettlementController extends Controller
{
    protected PartnerSettlementPeriodRepositoryInterface $periodRepo;
    protected SettlementLineItemRepositoryInterface $lineItemRepo;
    protected SettlementService $settlementService;
    protected RevenueReportingService $reportingService;

    public function __construct(
        PartnerSettlementPeriodRepositoryInterface $periodRepo,
        SettlementLineItemRepositoryInterface $lineItemRepo,
        SettlementService $settlementService,
        RevenueReportingService $reportingService
    ) {
        $this->periodRepo = $periodRepo;
        $this->lineItemRepo = $lineItemRepo;
        $this->settlementService = $settlementService;
        $this->reportingService = $reportingService;
    }

    /**
     * Lấy danh sách kỳ đối soát kèm bộ lọc và phân trang.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'partner_id',
            'status',
            'start_date',
            'end_date',
            'sort_by',
            'direction',
            'pagination',
        ]);

        $paginated = $this->periodRepo->paginateWithFilters($filters);

        // Biến đổi dữ liệu bổ sung trường net_commission_to_pay, total_adjustments
        $items = collect($paginated->items())->map(static function ($period) {
            $periodArray = $period->toArray();
            $periodArray['total_adjustments'] = $period->total_adjustments;
            $periodArray['net_commission_to_pay'] = $period->net_commission_to_pay;
            return $periodArray;
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'items' => $items,
                'meta'  => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                ]
            ],
            'message' => 'Lấy danh sách đối soát thành công.'
        ]);
    }

    /**
     * Xem thông tin chi tiết của một kỳ đối soát.
     */
    public function show(int $id): JsonResponse
    {
        $period = $this->periodRepo->find($id);

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kỳ đối soát.'
            ], 404);
        }

        $period->load(['partner', 'adjustments.creator', 'confirmedBy']);

        $data = $period->toArray();
        $data['total_adjustments'] = $period->total_adjustments;
        $data['net_commission_to_pay'] = $period->net_commission_to_pay;

        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => 'Lấy chi tiết kỳ đối soát thành công.'
        ]);
    }

    /**
     * Lấy danh sách chi tiết đơn đặt phòng (line items) trong kỳ đối soát.
     */
    public function lineItems(Request $request, int $id): JsonResponse
    {
        $period = $this->periodRepo->find($id);

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kỳ đối soát.'
            ], 404);
        }

        $filters = $request->only(['sort_by', 'direction', 'pagination']);
        $paginated = $this->lineItemRepo->paginateByPeriod($id, $filters);

        return response()->json([
            'success' => true,
            'data'    => [
                'items' => $paginated->items(),
                'meta'  => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                ]
            ],
            'message' => 'Lấy chi tiết bảng kê thành công.'
        ]);
    }

    /**
     * Phát hành kỳ đối soát để gửi thông báo cho đối tác.
     */
    public function issue(int $id): JsonResponse
    {
        try {
            $period = $this->settlementService->issuePeriod($id);

            return response()->json([
                'success' => true,
                'data'    => $period,
                'message' => 'Phát hành kỳ đối soát thành công.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Xác nhận đối tác đã thanh toán nợ phí thành công.
     */
    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_reference' => 'required|string|max:100',
            'note'              => 'nullable|string|max:1000',
        ], [
            'payment_reference.required' => 'Mã giao dịch chuyển khoản là bắt buộc.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $adminId = Auth::id() ?? 1; // Fallback admin id
            $period = $this->settlementService->confirmPayment($id, [
                'payment_reference' => $request->input('payment_reference'),
                'confirmed_by'      => (int) $adminId,
                'note'              => $request->input('note'),
            ]);

            return response()->json([
                'success' => true,
                'data'    => $period,
                'message' => 'Xác nhận thanh toán thành công.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Thêm một khoản điều chỉnh tăng/giảm hoa hồng quyết toán.
     */
    public function addAdjustment(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|max:1000',
        ], [
            'amount.required' => 'Số tiền điều chỉnh là bắt buộc.',
            'amount.not_in'   => 'Số tiền điều chỉnh phải khác 0.',
            'reason.required' => 'Lý do điều chỉnh là bắt buộc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $adminId = Auth::id() ?? 1;
            $adjustment = $this->settlementService->addAdjustment($id, [
                'amount'     => (float) $request->input('amount'),
                'reason'     => $request->input('reason'),
                'created_by' => (int) $adminId,
            ]);

            return response()->json([
                'success' => true,
                'data'    => $adjustment,
                'message' => 'Thêm dòng điều chỉnh công nợ thành công.'
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Lấy tổng quan các chỉ số tài chính Admin Dashboard.
     */
    public function summary(): JsonResponse
    {
        $summary = $this->reportingService->getAdminRevenueSummary();

        return response()->json([
            'success' => true,
            'data'    => $summary,
            'message' => 'Lấy tổng hợp chỉ số tài chính thành công.'
        ]);
    }

    /**
     * Báo cáo doanh thu và hoa hồng theo ngày.
     */
    public function dailyReport(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        $report = $this->reportingService->getRevenueDailyReport((string) $startDate, (string) $endDate);

        return response()->json([
            'success' => true,
            'data'    => $report,
            'message' => 'Lấy báo cáo doanh thu hàng ngày thành công.'
        ]);
    }

    /**
     * Báo cáo doanh thu và hoa hồng theo tháng.
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $year = $request->input('year', Carbon::now()->format('Y'));

        $report = $this->reportingService->getRevenueMonthlyReport((string) $year);

        return response()->json([
            'success' => true,
            'data'    => $report,
            'message' => 'Lấy báo cáo doanh thu theo tháng thành công.'
        ]);
    }
}

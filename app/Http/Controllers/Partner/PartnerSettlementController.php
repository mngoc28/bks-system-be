<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\SettlementService;
use App\Repositories\PartnerSettlementPeriodRepository\PartnerSettlementPeriodRepositoryInterface;
use App\Repositories\SettlementLineItemRepository\SettlementLineItemRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller dành cho Đối tác xem và quản lý đối soát tài chính của mình.
 */
class PartnerSettlementController extends Controller
{
    protected PartnerSettlementPeriodRepositoryInterface $periodRepo;
    protected SettlementLineItemRepositoryInterface $lineItemRepo;
    protected SettlementService $settlementService;

    public function __construct(
        PartnerSettlementPeriodRepositoryInterface $periodRepo,
        SettlementLineItemRepositoryInterface $lineItemRepo,
        SettlementService $settlementService
    ) {
        $this->periodRepo = $periodRepo;
        $this->lineItemRepo = $lineItemRepo;
        $this->settlementService = $settlementService;
    }

    /**
     * Lấy danh sách các kỳ đối soát của đối tác đang đăng nhập.
     */
    public function index(Request $request): JsonResponse
    {
        $partnerId = Auth::id();

        $filters = $request->only([
            'status',
            'start_date',
            'end_date',
            'sort_by',
            'direction',
            'pagination',
        ]);

        // Luôn ghi đè partner_id để tránh đối tác nhìn thấy dữ liệu của người khác
        $filters['partner_id'] = $partnerId;

        $paginated = $this->periodRepo->paginateWithFilters($filters);

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
            'message' => 'Lấy danh sách đối soát của đối tác thành công.'
        ]);
    }

    /**
     * Xem chi tiết một kỳ đối soát của đối tác.
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

        // Kiểm tra quyền sở hữu
        if ((int) $period->partner_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập thông tin đối soát này.'
            ], 403);
        }

        $period->load(['adjustments.creator', 'confirmedBy']);

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
     * Lấy danh sách chi tiết đơn đặt phòng (line items) trong kỳ đối soát của đối tác.
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

        // Kiểm tra quyền sở hữu
        if ((int) $period->partner_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập chi tiết đối soát này.'
            ], 403);
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
     * Đối tác gửi khiếu nại về kỳ đối soát.
     */
    public function dispute(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Lý do khiếu nại là bắt buộc.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $period = $this->periodRepo->find($id);

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kỳ đối soát.'
            ], 404);
        }

        // Kiểm tra quyền sở hữu
        if ((int) $period->partner_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền khiếu nại kỳ đối soát này.'
            ], 403);
        }

        try {
            $updatedPeriod = $this->settlementService->disputePeriod($id, $request->input('reason'));

            return response()->json([
                'success' => true,
                'data'    => $updatedPeriod,
                'message' => 'Gửi khiếu nại thành công. Bảng kê đối soát đã chuyển sang trạng thái tranh chấp.'
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Xuất Excel bảng kê kỳ đối soát.
     * (Sẽ được viết logic ở T3.3)
     */
    public function exportExcel(int $id)
    {
        $period = $this->periodRepo->find($id);

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kỳ đối soát.'
            ], 404);
        }

        // Kiểm tra quyền sở hữu (Nếu không phải admin và partner khác sở hữu)
        if (Auth::user()->role !== 'admin' && (int) $period->partner_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xuất dữ liệu kỳ đối soát này.'
            ], 403);
        }

        // Gọi Excel export
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SettlementLineItemsExport($id),
            sprintf('BKS-Đối-Soát-Kỳ-%d.xlsx', $id)
        );
    }

    /**
     * Xuất PDF bảng kê đối soát.
     * (Sẽ được viết logic ở T3.4)
     */
    public function exportPdf(int $id)
    {
        $period = $this->periodRepo->find($id);

        if (!$period) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kỳ đối soát.'
            ], 404);
        }

        if (Auth::user()->role !== 'admin' && (int) $period->partner_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xuất dữ liệu kỳ đối soát này.'
            ], 403);
        }

        $period->load(['partner', 'adjustments.creator', 'confirmedBy']);
        $lineItems = \App\Models\SettlementLineItem::query()
            ->where('settlement_period_id', $id)
            ->with(['booking.room.property'])
            ->get();

        $bankInfo = config('billing.bank_info');

        // Khắc phục lỗi nếu issue_date rỗng/null (phòng tránh lỗi null pointer)
        $issueDate = $period->issue_date ? $period->issue_date->copy() : now();
        $dueDate = $issueDate->addDays(config('billing.due_days', 5))->format('d/m/Y');
        $transferSyntax = sprintf('%s%d', $bankInfo['transfer_syntax_prefix'], $period->id);

        $html = view('exports.settlement_pdf', [
            'period'         => $period,
            'lineItems'      => $lineItems,
            'bankInfo'       => $bankInfo,
            'dueDate'        => $dueDate,
            'transferSyntax' => $transferSyntax,
        ])->render();

        $mpdf = new \Mpdf\Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);
        $mpdf->WriteHTML($html);
        $pdfContent = $mpdf->Output('', 'S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . sprintf('BKS-Bảng-Kê-Đối-Soát-Kỳ-%d.pdf', $id) . '"'
        ]);
    }
}

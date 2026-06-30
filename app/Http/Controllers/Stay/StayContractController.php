<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stay;

use App\Enums\BookingStatus;
use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\StayService;
use App\Services\BookingPaymentStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class StayContractController extends Controller
{
    /**
     * @var StayService
     */
    protected $stayService;

    /**
     * Constructor
     *
     * @param StayService $stayService
     */
    public function __construct(StayService $stayService)
    {
        $this->stayService = $stayService;
    }

    /**
     * Get user's contracts
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        $contracts = $this->stayService->getContracts($userId);
        return $this->successResponse($contracts, 'Contracts retrieved successfully');
    }

    /**
     * Get contract detail
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        $contract = $this->stayService->getContractDetail($id, $userId);

        if (!$contract) {
            return $this->errorResponse('Contract not found', null, HttpStatus::NOT_FOUND);
        }

        return $this->successResponse($contract, 'Contract details retrieved successfully');
    }

    /**
     * Sign Contract (for LEASE_AGREEMENT)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function sign(int $id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return $this->errorResponse('Unauthorized', null, HttpStatus::UNAUTHORIZED);
        }

        try {
            // Find contract belonging to user's bookings
            $contract = \App\Models\Contract::where('id', $id)
                ->whereHas('booking', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->with('booking')
                ->first();

            if (!$contract) {
                return $this->errorResponse('Contract not found or access denied', null, HttpStatus::NOT_FOUND);
            }

            if (!$contract->booking || (int) $contract->booking->status !== BookingStatus::CONFIRMED->value) {
                return $this->errorResponse(
                    'Đơn đặt phòng chưa được Partner xác nhận. Vui lòng đợi trước khi ký hợp đồng.',
                    null,
                    HttpStatus::BAD_REQUEST
                );
            }

            if ($contract->status === 1) {
                return $this->errorResponse('Contract is already signed', null, HttpStatus::BAD_REQUEST);
            }

            $contract->update([
                'status'         => 1,
                'signature'      => request()->input('signature'),
                'signature_date' => \Carbon\Carbon::now(),
            ]);

            // Cập nhật trạng thái Booking sang Đã xác nhận (Status 1) và đồng bộ payment_status
            if ($contract->booking) {
                $contract->booking->update([
                    'status' => BookingStatus::CONFIRMED->value,
                ]);
                $contract->booking->refresh();
                BookingPaymentStatusService::sync($contract->booking);
            }

            return $this->successResponse($contract, 'Contract signed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to sign contract', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }
}

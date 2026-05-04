<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Repositories\ContractRepository\ContractRepositoryInterface;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Collection;

final class ContractService
{
    /**
     * @var ContractRepositoryInterface
     */
    protected $contractRepository;

    /**
     * @var BookingRepositoryInterface
     */
    protected $bookingRepository;

    /**
     * ContractService constructor.
     *
     * @param ContractRepositoryInterface $contractRepository
     * @param BookingRepositoryInterface $bookingRepository
     */
    public function __construct(
        ContractRepositoryInterface $contractRepository,
        BookingRepositoryInterface $bookingRepository
    ) {
        $this->contractRepository = $contractRepository;
        $this->bookingRepository  = $bookingRepository;
    }

    /**
     * Get all contracts for the authenticated partner
     *
     * @return array
     */
    public function handleGetPartnerContracts(): array
    {
        try {
            $partnerId = Auth::id();
            $contracts = $this->contractRepository->getContractsForPartner($partnerId);

            return [
                'success' => true,
                'data'    => $contracts,
                'message' => 'Lấy danh sách hợp đồng thành công.',
            ];
        } catch (Exception $e) {
            Log::error("Partner get contracts failed: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Lấy danh sách hợp đồng thất bại.',
            ];
        }
    }

    /**
     * Get contract detail for partner
     *
     * @param int $id
     * @return array
     */
    public function handleGetPartnerContractDetail(int $id): array
    {
        try {
            $partnerId = Auth::id();
            $contract  = $this->contractRepository->getPartnerContractDetail($id, $partnerId);

            if (! $contract) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Không tìm thấy hợp đồng.',
                ];
            }

            return [
                'success' => true,
                'data'    => $contract,
                'message' => 'Lấy chi tiết hợp đồng thành công.',
            ];
        } catch (Exception $e) {
            Log::error("Partner get contract detail failed: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Lấy chi tiết hợp đồng thất bại.',
            ];
        }
    }

    /**
     * Create a new contract
     *
     * @param $request
     * @return array
     */
    public function handleCreateContract($request): array
    {
        try {
            $partnerId = Auth::id();

            // Verify booking belongs to this partner
            $booking = $this->bookingRepository->find($request->booking_id);
            if (!$booking || $booking->room->building->user_id !== $partnerId) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Yêu cầu đặt phòng không hợp lệ hoặc không thuộc quyền quản lý của bạn.',
                ];
            }

            if ((int) $booking->status !== BookingStatus::CONFIRMED->value) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => 'Chỉ có thể tạo hợp đồng từ booking đã xác nhận.',
                ];
            }

            $contract = $this->contractRepository->create([
                'booking_id' => $request->booking_id,
                'title'      => $request->title,
                'content'    => $request->content,
                'status'     => 1, // Active/Pending
                'type'       => $request->type ?? 'Rental',
                'created_by' => $partnerId,
                'updated_by' => $partnerId,
            ]);

            return [
                'success' => true,
                'data'    => $contract,
                'message' => 'Tạo hợp đồng thành công.',
            ];
        } catch (Exception $e) {
            Log::error("Partner create contract failed: " . $e->getMessage());
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Tạo hợp đồng thất bại: ' . $e->getMessage(),
            ];
        }
    }
}

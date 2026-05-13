<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Events\ContractRenewalReminderQueued;
use App\Models\Contract;
use App\Repositories\ContractRepository\ContractRepositoryInterface;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Carbon as IlluminateCarbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Throwable;

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
     * @return array{success: bool, data: mixed, message: string}
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
     * @return array{success: bool, data: mixed, message: string}
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
     * @return array{success: bool, data: mixed, message: string}
     */
    public function handleCreateContract($request): array
    {
        try {
            $partnerId = Auth::id();

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
                'status'     => 1,
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

    /**
     * Set the renewal reminder timestamp on a long-term contract. Used both by
     * the daily scheduler and by partner manual actions. Idempotent: if the
     * reminder slot is already filled, the existing value is returned.
     *
     * @return array{success: bool, data: ?Contract, message: string, code?: string}
     */
    public function setRenewalReminder(int $contractId, Carbon $remindAt, bool $authorize = true): array
    {
        $contract = $this->contractRepository->find($contractId);
        if ($contract === null) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Không tìm thấy hợp đồng.',
                'code'    => 'CONTRACT_NOT_FOUND',
            ];
        }

        if ($authorize && ! Gate::allows('manageRenewal', $contract)) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Bạn không có quyền cập nhật hợp đồng này.',
                'code'    => 'CONTRACT_FORBIDDEN',
            ];
        }

        if ((string) $contract->contract_type !== 'LEASE_AGREEMENT') {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Chỉ hợp đồng dài hạn (LEASE_AGREEMENT) mới có nhắc gia hạn.',
                'code'    => 'CONTRACT_NOT_LEASE',
            ];
        }

        if ($contract->terminated_at !== null) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Hợp đồng đã chấm dứt, không thể nhắc gia hạn.',
                'code'    => 'CONTRACT_TERMINATED',
            ];
        }

        try {
            DB::transaction(function () use ($contract, $contractId, $remindAt): void {
                $this->contractRepository->update($contractId, [
                    'renewal_reminder_at' => $remindAt,
                    'updated_by'          => Auth::id() ?? $contract->updated_by,
                ]);
                $contract->renewal_reminder_at = $remindAt;
            });

            $this->dispatchReminderEvent($contract);

            return [
                'success' => true,
                'data'    => $contract,
                'message' => 'Đã đặt nhắc gia hạn hợp đồng.',
            ];
        } catch (Throwable $e) {
            Log::error('ContractService::setRenewalReminder failed', [
                'contract_id' => $contractId,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => 'Không thể cập nhật nhắc gia hạn.',
                'code'    => 'CONTRACT_REMINDER_FAILED',
            ];
        }
    }

    /**
     * Terminate an active contract. Requires a non-empty reason which is
     * persisted alongside `terminated_at`.
     *
     * @return array{success: bool, data: ?Contract, message: string, code?: string}
     */
    public function terminate(int $contractId, string $reason, bool $authorize = true): array
    {
        $contract = $this->contractRepository->find($contractId);
        if ($contract === null) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Không tìm thấy hợp đồng.',
                'code'    => 'CONTRACT_NOT_FOUND',
            ];
        }

        if ($authorize && ! Gate::allows('terminate', $contract)) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Bạn không có quyền chấm dứt hợp đồng này.',
                'code'    => 'CONTRACT_FORBIDDEN',
            ];
        }

        if ($contract->terminated_at !== null) {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Hợp đồng đã được chấm dứt trước đó.',
                'code'    => 'CONTRACT_ALREADY_TERMINATED',
            ];
        }

        $reason = trim($reason);
        if ($reason === '') {
            return [
                'success' => false,
                'data'    => null,
                'message' => 'Vui lòng nhập lý do chấm dứt hợp đồng.',
                'code'    => 'CONTRACT_TERMINATE_REASON_REQUIRED',
            ];
        }

        try {
            $now = Carbon::now();
            DB::transaction(function () use ($contract, $contractId, $reason, $now): void {
                $this->contractRepository->update($contractId, [
                    'terminated_at'      => $now,
                    'termination_reason' => $reason,
                    'updated_by'         => Auth::id() ?? $contract->updated_by,
                ]);
                $contract->terminated_at = $now;
                $contract->termination_reason = $reason;
            });

            return [
                'success' => true,
                'data'    => $contract,
                'message' => 'Đã chấm dứt hợp đồng.',
            ];
        } catch (Throwable $e) {
            Log::error('ContractService::terminate failed', [
                'contract_id' => $contractId,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => 'Không thể chấm dứt hợp đồng.',
                'code'    => 'CONTRACT_TERMINATE_FAILED',
            ];
        }
    }

    /**
     * Return contracts that the Alert Center treats as "expiring soon" for
     * the authenticated partner.
     *
     * @return array{success: bool, data: mixed, message: string}
     */
    public function handleGetExpiringContractsForPartner(): array
    {
        try {
            $partnerId = (int) Auth::id();
            $contracts = $this->contractRepository->getExpiringContractsForPartner($partnerId);

            $payload = $contracts->map(function (Contract $contract): array {
                $booking = $contract->booking;
                $room = $booking?->room;
                $building = $room?->building;

                return [
                    'id'                  => (int) $contract->id,
                    'title'               => (string) $contract->title,
                    'contract_type'       => (string) $contract->contract_type,
                    'renewal_reminder_at' => optional($contract->renewal_reminder_at)->toIso8601String(),
                    'terminated_at'       => optional($contract->terminated_at)->toIso8601String(),
                    'booking_id'          => $booking ? (int) $booking->id : null,
                    'booking_end_date'    => $booking
                        ? IlluminateCarbon::parse($booking->end_date)->toDateString()
                        : null,
                    'room_label'          => $room?->title,
                    'building_name'       => $building?->name,
                    'guest_name'          => optional($booking?->user)->name,
                ];
            })->values()->all();

            return [
                'success' => true,
                'data'    => $payload,
                'message' => 'Lấy danh sách hợp đồng sắp hết hạn thành công.',
            ];
        } catch (Throwable $e) {
            Log::error('ContractService::handleGetExpiringContractsForPartner failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => 'Không thể lấy danh sách hợp đồng sắp hết hạn.',
            ];
        }
    }

    /**
     * Process every long-term contract whose booking is within `$daysAhead`
     * days of `end_date` and tag them with a reminder. Returns the number of
     * contracts processed (for scheduler logging).
     */
    public function processDueReminders(int $daysAhead = 30): int
    {
        $contracts = $this->contractRepository->getLongTermContractsDueForReminder($daysAhead);
        $count = 0;

        foreach ($contracts as $contract) {
            $result = $this->setRenewalReminder($contract->id, Carbon::now(), false);
            if ($result['success']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Dispatch `ContractRenewalReminderQueued` for the given contract so the
     * Partner Alert Center can refresh in realtime. Silently no-ops on
     * broadcast failures (e.g. log driver) — the cache layer is still
     * authoritative via API polling.
     */
    private function dispatchReminderEvent(Contract $contract): void
    {
        try {
            $contract->loadMissing(['booking.room.building']);
            $booking = $contract->booking;
            $room = $booking?->room;
            $building = $room?->building;
            if ($building === null || $booking === null) {
                return;
            }

            ContractRenewalReminderQueued::dispatch(
                $contract,
                (int) $building->user_id,
                $building->id !== null ? (int) $building->id : null,
                IlluminateCarbon::parse($booking->end_date)->toDateString(),
            );
        } catch (Throwable $e) {
            Log::warning('ContractRenewalReminderQueued dispatch skipped', [
                'contract_id' => $contract->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}

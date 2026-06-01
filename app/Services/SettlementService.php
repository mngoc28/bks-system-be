<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\SettlementPeriodIssued;
use App\Models\PartnerSettlementPeriod;
use App\Models\SettlementAdjustment;
use App\Repositories\PartnerSettlementPeriodRepository\PartnerSettlementPeriodRepositoryInterface;
use App\Repositories\SettlementAdjustmentRepository\SettlementAdjustmentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Service xử lý các nghiệp vụ đối soát công nợ hoa hồng của đối tác.
 */
class SettlementService
{
    /**
     * @var \App\Repositories\PartnerSettlementPeriodRepository\PartnerSettlementPeriodRepositoryInterface
     */
    protected PartnerSettlementPeriodRepositoryInterface $periodRepo;

    /**
     * @var \App\Repositories\SettlementAdjustmentRepository\SettlementAdjustmentRepositoryInterface
     */
    protected SettlementAdjustmentRepositoryInterface $adjustmentRepo;

    /**
     * Khởi tạo service với các repositories được inject.
     */
    public function __construct(
        PartnerSettlementPeriodRepositoryInterface $periodRepo,
        SettlementAdjustmentRepositoryInterface $adjustmentRepo
    ) {
        $this->periodRepo = $periodRepo;
        $this->adjustmentRepo = $adjustmentRepo;
    }

    /**
     * Phát hành kỳ đối soát (chuyển sang trạng thái issued).
     *
     * @param int $periodId
     * @return \App\Models\PartnerSettlementPeriod
     * @throws \InvalidArgumentException
     */
    public function issuePeriod(int $periodId): PartnerSettlementPeriod
    {
        return DB::transaction(function () use ($periodId) {
            /** @var \App\Models\PartnerSettlementPeriod|null $period */
            $period = $this->periodRepo->find($periodId);

            if (!$period) {
                throw new InvalidArgumentException('Kỳ đối soát không tồn tại.');
            }

            $allowedStatuses = [
                PartnerSettlementPeriod::STATUS_DRAFT,
                PartnerSettlementPeriod::STATUS_DISPUTED,
            ];
            if (!in_array($period->status, $allowedStatuses, true)) {
                throw new InvalidArgumentException(
                    'Chỉ có thể phát hành kỳ đối soát ở trạng thái Nháp (Draft) hoặc Đang khiếu nại (Disputed).'
                );
            }

            $this->periodRepo->update($periodId, [
                'status'    => PartnerSettlementPeriod::STATUS_ISSUED,
                'issued_at' => Carbon::now(),
            ]);
            $updatedPeriod = $this->periodRepo->find($periodId);

            Log::info("SettlementService: Phát hành kỳ đối soát #{$periodId} thành công.");

            // Dispatch event gửi mail thông báo nợ phí cho đối tác
            event(new SettlementPeriodIssued($updatedPeriod));

            return $updatedPeriod;
        });
    }

    /**
     * Đối tác gửi khiếu nại đối soát (chuyển sang trạng thái disputed).
     *
     * @param int $periodId
     * @param string $reason
     * @return \App\Models\PartnerSettlementPeriod
     * @throws \InvalidArgumentException
     */
    public function disputePeriod(int $periodId, string $reason): PartnerSettlementPeriod
    {
        if (empty(trim($reason))) {
            throw new InvalidArgumentException('Lý do khiếu nại không được để trống.');
        }

        return DB::transaction(function () use ($periodId, $reason) {
            /** @var \App\Models\PartnerSettlementPeriod|null $period */
            $period = $this->periodRepo->find($periodId);

            if (!$period) {
                throw new InvalidArgumentException('Kỳ đối soát không tồn tại.');
            }

            if ($period->status !== PartnerSettlementPeriod::STATUS_ISSUED) {
                throw new InvalidArgumentException('Chỉ có thể khiếu nại kỳ đối soát đã phát hành (Issued).');
            }

            $this->periodRepo->update($periodId, [
                'status' => PartnerSettlementPeriod::STATUS_DISPUTED,
                'note'   => trim($period->note . "\n[Khiếu nại đối tác] " . $reason),
            ]);
            $updatedPeriod = $this->periodRepo->find($periodId);

            Log::info("SettlementService: Đối tác đã khiếu nại kỳ đối soát #{$periodId}.", ['reason' => $reason]);

            return $updatedPeriod;
        });
    }

    /**
     * Xác nhận thanh toán (chuyển sang trạng thái paid).
     *
     * @param int $periodId
     * @param array{payment_reference: string, confirmed_by: int, note?: string} $data
     * @return \App\Models\PartnerSettlementPeriod
     * @throws \InvalidArgumentException
     */
    public function confirmPayment(int $periodId, array $data): PartnerSettlementPeriod
    {
        if (empty($data['payment_reference'])) {
            throw new InvalidArgumentException('Mã giao dịch chuyển khoản (payment_reference) là bắt buộc.');
        }

        if (empty($data['confirmed_by'])) {
            throw new InvalidArgumentException('Admin xác nhận (confirmed_by) là bắt buộc.');
        }

        return DB::transaction(function () use ($periodId, $data) {
            /** @var \App\Models\PartnerSettlementPeriod|null $period */
            $period = $this->periodRepo->find($periodId);

            if (!$period) {
                throw new InvalidArgumentException('Kỳ đối soát không tồn tại.');
            }

            $validStatuses = [
                PartnerSettlementPeriod::STATUS_ISSUED,
                PartnerSettlementPeriod::STATUS_DISPUTED,
            ];
            if (!in_array($period->status, $validStatuses, true)) {
                throw new InvalidArgumentException(
                    'Chỉ có thể xác nhận thanh toán cho kỳ đối soát đã phát hành hoặc đang khiếu nại.'
                );
            }

            $note = $period->note;
            if (!empty($data['note'])) {
                $note = trim($note . "\n[Xác nhận thanh toán] " . $data['note']);
            }

            $this->periodRepo->update($periodId, [
                'status'            => PartnerSettlementPeriod::STATUS_PAID,
                'paid_at'           => Carbon::now(),
                'payment_reference' => $data['payment_reference'],
                'confirmed_by'      => $data['confirmed_by'],
                'note'              => $note,
            ]);
            $updatedPeriod = $this->periodRepo->find($periodId);

            Log::info("SettlementService: Xác nhận thanh toán kỳ đối soát #{$periodId} thành công.", [
                'ref' => $data['payment_reference'],
                'by'  => $data['confirmed_by']
            ]);

            return $updatedPeriod;
        });
    }

    /**
     * Đóng kỳ đối soát (chuyển sang trạng thái closed).
     *
     * @param int $periodId
     * @return \App\Models\PartnerSettlementPeriod
     * @throws \InvalidArgumentException
     */
    public function closePeriod(int $periodId): PartnerSettlementPeriod
    {
        return DB::transaction(function () use ($periodId) {
            /** @var \App\Models\PartnerSettlementPeriod|null $period */
            $period = $this->periodRepo->find($periodId);

            if (!$period) {
                throw new InvalidArgumentException('Kỳ đối soát không tồn tại.');
            }

            if ($period->status !== PartnerSettlementPeriod::STATUS_PAID) {
                throw new InvalidArgumentException('Chỉ có thể đóng kỳ đối soát đã thanh toán (Paid).');
            }

            $this->periodRepo->update($periodId, [
                'status' => PartnerSettlementPeriod::STATUS_CLOSED,
            ]);
            $updatedPeriod = $this->periodRepo->find($periodId);

            Log::info("SettlementService: Đóng kỳ đối soát #{$periodId} thành công.");

            return $updatedPeriod;
        });
    }

    /**
     * Thêm dòng điều chỉnh công nợ.
     *
     * @param int $periodId
     * @param array{amount: float, reason: string, created_by: int} $data
     * @return \App\Models\SettlementAdjustment
     * @throws \InvalidArgumentException
     */
    public function addAdjustment(int $periodId, array $data): SettlementAdjustment
    {
        if (empty($data['amount'])) {
            throw new InvalidArgumentException('Số tiền điều chỉnh phải khác 0.');
        }

        if (empty($data['reason'])) {
            throw new InvalidArgumentException('Lý do điều chỉnh không được để trống.');
        }

        if (empty($data['created_by'])) {
            throw new InvalidArgumentException('Admin tạo điều chỉnh (created_by) là bắt buộc.');
        }

        return DB::transaction(function () use ($periodId, $data) {
            /** @var \App\Models\PartnerSettlementPeriod|null $period */
            $period = $this->periodRepo->find($periodId);

            if (!$period) {
                throw new InvalidArgumentException('Kỳ đối soát không tồn tại.');
            }

            // Chỉ cho phép điều chỉnh khi kỳ chưa hoàn thành thanh toán
            $invalidStatuses = [
                PartnerSettlementPeriod::STATUS_PAID,
                PartnerSettlementPeriod::STATUS_CLOSED,
            ];
            if (in_array($period->status, $invalidStatuses, true)) {
                throw new InvalidArgumentException(
                    'Không thể thêm điều chỉnh công nợ cho kỳ đối soát đã thanh toán hoặc đã đóng.'
                );
            }

            $adjustment = $this->adjustmentRepo->create([
                'settlement_period_id' => $periodId,
                'amount'               => (float) $data['amount'],
                'reason'               => trim($data['reason']),
                'created_by'           => $data['created_by'],
            ]);

            Log::info(
                "SettlementService: Đã thêm điều chỉnh công nợ #{$adjustment->id} " .
                "trị giá {$data['amount']} cho kỳ #{$periodId}."
            );

            return $adjustment;
        });
    }
}

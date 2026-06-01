<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDeposit;
use App\Repositories\BookingDepositRepository\BookingDepositRepositoryInterface;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class DepositService
{
    public function __construct(
        private readonly BookingDepositRepositoryInterface $depositRepository,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly DynamicDepositPolicyService $policyService,
        private readonly BookingTimelineService $timelineService,
    ) {
    }

    /**
     * Create deposit request for booking if applicable.
     *
     * @param Booking $booking
     * @return BookingDeposit|null
     */
    public function createDeposit(Booking $booking): ?BookingDeposit
    {
        $room = $booking->room;
        $roomPrice = $booking->price;

        if (!$room) {
            return null;
        }

        $policyResult = $this->policyService->calculateRequiredDeposit(
            $room,
            $roomPrice,
            (string) $booking->getRawOriginal('start_date'),
            (string) $booking->getRawOriginal('end_date')
        );

        if ($policyResult['required'] && $policyResult['amount'] > 0) {
            DB::beginTransaction();
            try {
                $deposit = $this->depositRepository->create([
                    'booking_id' => $booking->id,
                    'amount' => $policyResult['amount'],
                    'status' => 'pending',
                ]);

                $booking->update([
                    'deposit_amount' => $policyResult['amount'],
                    'deposit_status' => 'pending',
                ]);

                $this->timelineService->recordCreated($booking->id, null, [
                    'deposit_required' => true,
                    'deposit_amount' => $policyResult['amount'],
                ]);

                DB::commit();
                return $deposit;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to create booking deposit: ' . $e->getMessage());
                return null;
            }
        }

        // If not required, set deposit fields to none/0
        $booking->update([
            'deposit_amount' => 0.0,
            'deposit_status' => 'none',
        ]);

        return null;
    }

    /**
     * Guest submits payment receipt for the deposit.
     *
     * @param int $bookingId
     * @param string $receiptPath
     * @return bool
     */
    public function submitReceipt(int $bookingId, string $receiptPath): bool
    {
        $deposit = $this->depositRepository->findOneBy(['booking_id' => $bookingId], false);
        if (!$deposit) {
            return false;
        }

        DB::beginTransaction();
        try {
            $deposit->update([
                'receipt_path' => $receiptPath,
                'status' => 'payment_submitted',
            ]);

            $booking = $this->bookingRepository->find($bookingId);
            if ($booking) {
                $booking->update([
                    'deposit_status' => 'payment_submitted',
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit deposit receipt: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Partner (Host) confirms the receipt of deposit.
     *
     * @param int $bookingId
     * @return bool
     */
    public function confirmReceiptByPartner(int $bookingId): bool
    {
        $deposit = $this->depositRepository->findOneBy(['booking_id' => $bookingId], false);
        if (!$deposit) {
            return false;
        }

        // Check if long term, set to held_in_escrow, otherwise confirmed_by_partner
        $booking = $this->bookingRepository->find($bookingId);
        if (!$booking) {
            return false;
        }

        $roomPrice = $booking->price;
        $isLongTerm = $roomPrice && $roomPrice->unit === 'month';
        $status = $isLongTerm ? 'held_in_escrow' : 'confirmed_by_partner';

        DB::beginTransaction();
        try {
            $deposit->update([
                'status' => $status,
            ]);

            $booking->update([
                'deposit_status' => $status,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm deposit receipt: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Refund deposit to guest.
     *
     * @param int $bookingId
     * @return bool
     */
    public function refundDeposit(int $bookingId): bool
    {
        $deposit = $this->depositRepository->findOneBy(['booking_id' => $bookingId], false);
        if (!$deposit) {
            return false;
        }

        DB::beginTransaction();
        try {
            $deposit->update([
                'status' => 'refunded',
            ]);

            $booking = $this->bookingRepository->find($bookingId);
            if ($booking) {
                $booking->update([
                    'deposit_status' => 'refunded',
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to refund deposit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Forfeit deposit (penalty for no-show/late cancel).
     *
     * @param int $bookingId
     * @return bool
     */
    public function forfeitDeposit(int $bookingId): bool
    {
        $deposit = $this->depositRepository->findOneBy(['booking_id' => $bookingId], false);
        if (!$deposit) {
            return false;
        }

        DB::beginTransaction();
        try {
            $deposit->update([
                'status' => 'forfeited',
            ]);

            $booking = $this->bookingRepository->find($bookingId);
            if ($booking) {
                $booking->update([
                    'deposit_status' => 'forfeited',
                ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to forfeit deposit: ' . $e->getMessage());
            return false;
        }
    }
}

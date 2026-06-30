<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use Carbon\Carbon;

/**
 * Đồng bộ payment_status với trạng thái cọc và số tiền đã thu.
 */
final class BookingPaymentStatusService
{
    /** @var list<string> */
    private const CONFIRMED_DEPOSIT_STATUSES = ['confirmed_by_partner', 'held_in_escrow'];

    public static function isDepositConfirmed(Booking $booking): bool
    {
        return in_array((string) ($booking->deposit_status ?? ''), self::CONFIRMED_DEPOSIT_STATUSES, true);
    }

    public static function getTotalAmount(Booking $booking): float
    {
        $booking->loadMissing(['price', 'services']);

        return BookingStayAmountCalculator::computeGrandTotalForBooking($booking);
    }

    public static function getAmountPaid(Booking $booking): float
    {
        $total = self::getTotalAmount($booking);
        $deposit = (float) ($booking->deposit_amount ?? 0);

        if ((string) $booking->payment_status === PaymentStatus::PAID->value) {
            if ($deposit > 0 && !self::isDepositConfirmed($booking)) {
                return 0.0;
            }

            return $total;
        }

        if (self::isDepositConfirmed($booking) && $deposit > 0) {
            return min($deposit, $total);
        }

        return 0.0;
    }

    public static function getAmountRemaining(Booking $booking): float
    {
        if (self::resolve($booking) === PaymentStatus::PAID->value) {
            return 0.0;
        }

        return max(0.0, self::getTotalAmount($booking) - self::getAmountPaid($booking));
    }

    public static function resolve(Booking $booking): string
    {
        if ((string) $booking->payment_status === PaymentStatus::REFUNDED->value) {
            return PaymentStatus::REFUNDED->value;
        }

        $total = self::getTotalAmount($booking);
        $deposit = (float) ($booking->deposit_amount ?? 0);

        if ((string) $booking->payment_status === PaymentStatus::PAID->value) {
            if ($deposit > 0 && !self::isDepositConfirmed($booking)) {
                return PaymentStatus::UNPAID->value;
            }

            return PaymentStatus::PAID->value;
        }

        if ($deposit > 0 && ($booking->deposit_status ?? 'none') !== 'none') {
            if (!self::isDepositConfirmed($booking)) {
                return PaymentStatus::UNPAID->value;
            }

            if ($deposit >= $total) {
                return PaymentStatus::PAID->value;
            }

            return PaymentStatus::PARTIALLY_PAID->value;
        }

        if ((string) $booking->payment_status === PaymentStatus::PAID->value) {
            return PaymentStatus::PAID->value;
        }

        return PaymentStatus::UNPAID->value;
    }

    public static function sync(Booking $booking): void
    {
        $resolved = self::resolve($booking);

        if ((string) $booking->payment_status !== $resolved) {
            $booking->update(['payment_status' => $resolved]);
            $booking->payment_status = $resolved;
        }
    }

    public static function markFullyPaid(Booking $booking): void
    {
        $booking->update([
            'payment_status'       => PaymentStatus::PAID->value,
            'payment_collected_at' => Carbon::now(),
        ]);
        $booking->payment_status = PaymentStatus::PAID->value;
    }

    public static function isRemainderPaymentPhase(Booking $booking): bool
    {
        return (int) $booking->status === 1
            && (string) $booking->payment_status === PaymentStatus::PARTIALLY_PAID->value
            && self::getAmountRemaining($booking) > 0;
    }
}

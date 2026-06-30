<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;

/**
 * REQ-DEP-002 — thuê dài hạn: chỉ cho thanh toán cọc sau khi đã ký LEASE_AGREEMENT.
 */
final class LeaseDepositGateService
{
    public const string DEPOSIT_BLOCKED_MESSAGE = 'Vui lòng ký hợp đồng thuê trước khi thanh toán tiền đặt cọc.';

    public static function canPayDeposit(Booking $booking): bool
    {
        if (!self::requiresSignedLeaseBeforeDeposit($booking)) {
            return true;
        }

        return self::hasSignedLeaseAgreement($booking);
    }

    public static function requiresSignedLeaseBeforeDeposit(Booking $booking): bool
    {
        if ((float) ($booking->deposit_amount ?? 0) <= 0) {
            return false;
        }

        $booking->loadMissing(['price', 'room.property.propertyType']);

        $propertyTypeSlug = $booking->room?->property?->propertyType?->slug;
        $startDate = (string) $booking->getRawOriginal('start_date');
        $endDate = (string) $booking->getRawOriginal('end_date');
        $stayNights = StayClassificationService::countStayNights($startDate, $endDate);
        $priceUnit = (string) ($booking->price?->unit ?? 'night');

        return StayClassificationService::isLongTermLeaseBooking(
            $propertyTypeSlug,
            $stayNights,
            $priceUnit,
        );
    }

    public static function hasSignedLeaseAgreement(Booking $booking): bool
    {
        return $booking->contracts()
            ->where('contract_type', 'LEASE_AGREEMENT')
            ->where('status', '!=', 0)
            ->exists();
    }

    public static function depositBlockReason(Booking $booking): ?string
    {
        if (self::canPayDeposit($booking)) {
            return null;
        }

        return self::DEPOSIT_BLOCKED_MESSAGE;
    }
}

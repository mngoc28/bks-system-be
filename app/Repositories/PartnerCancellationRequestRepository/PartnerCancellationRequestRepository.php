<?php

declare(strict_types=1);

namespace App\Repositories\PartnerCancellationRequestRepository;

use App\Models\BookingCancellationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class PartnerCancellationRequestRepository implements PartnerCancellationRequestRepositoryInterface
{
    /**
     * @param array{status?: string|null, property_id?: int|null, per_page?: int|null} $filters
     */
    public function paginateForPartner(int $partnerUserId, array $filters): LengthAwarePaginator
    {
        $status     = isset($filters['status']) ? (string) $filters['status'] : '';
        $propertyId = isset($filters['property_id']) ? (int) $filters['property_id'] : 0;
        $perPage    = isset($filters['per_page']) ? (int) $filters['per_page'] : 15;
        if ($perPage < 1) {
            $perPage = 15;
        }
        if ($perPage > 50) {
            $perPage = 50;
        }

        $allowedStatuses = ['pending', 'approved', 'rejected', 'withdrawn'];

        $query = BookingCancellationRequest::query()
            ->with([
                'booking.room.property',
            ])
            ->whereHas('booking.room.property', static function ($q) use ($partnerUserId): void {
                $q->where('user_id', $partnerUserId);
            })
            ->when(
                $status !== '' && in_array($status, $allowedStatuses, true),
                static function ($q) use ($status): void {
                    $q->where('status', $status);
                }
            )
            ->when($propertyId > 0, static function ($q) use ($propertyId): void {
                $q->whereHas('booking.room', static function ($r) use ($propertyId): void {
                    $r->where('property_id', $propertyId);
                });
            })
            ->orderByDesc('requested_at')
            ->orderByDesc('id');

        return $query->paginate($perPage);
    }

    public function findForPartner(int $partnerUserId, int $requestId): ?BookingCancellationRequest
    {
        return BookingCancellationRequest::query()
            ->whereKey($requestId)
            ->whereHas('booking.room.property', static function ($q) use ($partnerUserId): void {
                $q->where('user_id', $partnerUserId);
            })
            ->with(['booking.room.property'])
            ->first();
    }
}

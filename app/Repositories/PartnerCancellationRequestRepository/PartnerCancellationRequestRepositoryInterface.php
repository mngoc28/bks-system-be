<?php

declare(strict_types=1);

namespace App\Repositories\PartnerCancellationRequestRepository;

use App\Models\BookingCancellationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PartnerCancellationRequestRepositoryInterface
{
    /**
     * @param array{status?: string|null, property_id?: int|null, per_page?: int|null} $filters
     */
    public function paginateForPartner(int $partnerUserId, array $filters): LengthAwarePaginator;

    public function findForPartner(int $partnerUserId, int $requestId): ?BookingCancellationRequest;
}

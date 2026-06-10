<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\HttpStatus;
use App\Repositories\PropertyRepository\PropertyRepositoryInterface;

/**
 * Resolves optional dashboard `property_id` and enforces partner ownership.
 */
final class PartnerDashboardScopeResolver
{
    public function __construct(
        private readonly PropertyRepositoryInterface $propertyRepository,
    ) {
    }

    /**
     * @return array{propertyId: int|null, error: array{message: string, status: HttpStatus}|null}
     */
    public function resolvePropertyId(int $partnerId, ?int $propertyId): array
    {
        if ($propertyId === null) {
            return ['propertyId' => null, 'error' => null];
        }

        $property = $this->propertyRepository->getPropertyByIdForPartner($propertyId, $partnerId);

        if ($property === null) {
            return [
                'propertyId' => null,
                'error'    => [
                    'message' => __('dashboard.messages.property_not_accessible'),
                    'status'  => HttpStatus::FORBIDDEN,
                ],
            ];
        }

        return ['propertyId' => $propertyId, 'error' => null];
    }
}

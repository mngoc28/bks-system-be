<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\Partner\PartnerPropertyListResource;
use App\Http\Resources\Partner\PartnerRoomPreviewResource;
use App\Repositories\PropertyRepository\PropertyRepositoryInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class PartnerPropertyRoomPreviewService
{
    public function __construct(
        private readonly PropertyRepositoryInterface $propertyRepository
    ) {
    }

    /**
     * @return array{success: bool, data: array<string, mixed>|null, message: string}
     */
    public function getPreview(int $propertyId, int $limit): array
    {
        try {
            $partnerId = (int) Auth::id();
            $result    = $this->propertyRepository->getPropertyRoomPreviewForPartner(
                $propertyId,
                $partnerId,
                $limit
            );

            if ($result === null) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data'    => [
                    'property_id'   => $propertyId,
                    'total_rooms'   => (int) $result['property']->rooms_count,
                    'preview_limit' => $limit,
                    'rooms'         => PartnerRoomPreviewResource::collection($result['rooms'])->resolve(),
                ],
                'message' => __('property.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('Partner property room preview failed', [
                'property_id' => $propertyId,
                'partner_id'  => Auth::id(),
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.retrieved_failed'),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatPropertyPaginator(LengthAwarePaginator $properties): array
    {
        return [
            'current_page' => $properties->currentPage(),
            'data'         => PartnerPropertyListResource::collection($properties->getCollection())->resolve(),
            'last_page'    => $properties->lastPage(),
            'per_page'     => $properties->perPage(),
            'total'        => $properties->total(),
        ];
    }
}

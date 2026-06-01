<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\PropertyRepository\PropertyRepositoryInterface;
use App\Services\PartnerPropertyRoomPreviewService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class PropertiesService
{
    public function __construct(
        protected PropertyRepositoryInterface $propertyRepository,
        protected BookingRepositoryInterface $bookingRepository
    ) {
    }

    /**
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllOrSearchProperties(Request $request): array
    {
        try {
            $properties = $this->propertyRepository->getAllOrSearchProperties($request, (array) $request->sort);

            return [
                'success' => true,
                'data'    => $properties,
                'message' => __('property.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.retrieved_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.retrieved_failed'),
            ];
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllPropertyNames(): array
    {
        try {
            $properties = $this->propertyRepository->getAllPropertyNames();

            return [
                'success' => true,
                'data'    => $properties,
                'message' => __('property.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.retrieved_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.retrieved_failed'),
            ];
        }
    }

    public function getPropertyById(int $id): array
    {
        try {
            $property = $this->propertyRepository->getPropertyById($id);
            if (! $property) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data'    => $property,
                'message' => __('property.messages.found_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.find_failed'), [
                'property_id' => $id,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.find_failed'),
            ];
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string}
     */
    public function createProperty(array $data): array
    {
        try {
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $created = $this->propertyRepository->create($data);
            if (! $created) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property.messages.create_failed'),
                ];
            }

            return [
                'success' => true,
                'data'    => $created,
                'message' => __('property.messages.created_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.create_failed'), [
                'data'  => $data,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.create_failed'),
            ];
        }
    }

    public function updateProperty(int $id, array $data): array
    {
        try {
            $updated = $this->propertyRepository->update($id, array_merge($data, [
                'updated_by' => Auth::id(),
            ]));

            if (! $updated) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property.messages.update_failed'),
                ];
            }

            return [
                'success' => true,
                'data'    => $updated,
                'message' => __('property.messages.updated_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.update_failed'), [
                'property_id' => $id,
                'data'        => $data,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.update_failed'),
            ];
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string}
     */
    public function deleteProperty(int $id): array
    {
        try {
            $deleted = $this->propertyRepository->delete($id);

            if (! $deleted) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property.messages.delete_failed'),
                ];
            }

            return [
                'success' => true,
                'data'    => $deleted,
                'message' => __('property.messages.deleted_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.delete_failed'), [
                'property_id' => $id,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.delete_failed'),
            ];
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getBookingsByProperty(int $propertyId, $request): array
    {
        try {
            $request->merge(['property_id' => $propertyId]);

            $bookings = $this->bookingRepository->getAllOrSearchBookings($request);

            return [
                'success' => true,
                'data'    => $bookings,
                'message' => __('property.messages.bookings_retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.bookings_retrieved_failed'), [
                'property_id' => $propertyId,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.bookings_retrieved_failed'),
            ];
        }
    }

    /**
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getAllPropertyTypes(): array
    {
        try {
            $types = $this->propertyRepository->getAllPropertyTypes();

            return [
                'success' => true,
                'data'    => $types,
                'message' => __('property.messages.property_types_retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error(__('property.messages.property_types_retrieved_failed'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.property_types_retrieved_failed'),
            ];
        }
    }

    public function handleGetAllPropertiesForPartner(Request $request): array
    {
        try {
            $partnerId  = Auth::id();
            $properties = $this->propertyRepository->getPropertiesForPartner(
                $partnerId,
                $request,
                (array) $request->sort
            );

            return [
                'success' => true,
                'data'    => PartnerPropertyRoomPreviewService::formatPropertyPaginator($properties),
                'message' => __('property.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('Partner get properties failed', [
                'partner_id' => Auth::id(),
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.retrieved_failed'),
            ];
        }
    }

    public function handleGetPropertyDetailForPartner(int $id): array
    {
        try {
            $partnerId = Auth::id();
            $property  = $this->propertyRepository->getPropertyByIdForPartner($id, $partnerId);
            if (! $property) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data'    => $property,
                'message' => __('property.messages.found_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('Partner get property detail failed', [
                'property_id' => $id,
                'partner_id'  => Auth::id(),
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.find_failed'),
            ];
        }
    }

    public function handleGetPropertyNamesForPartner(): array
    {
        try {
            $partnerId  = Auth::id();
            $properties = $this->propertyRepository->getPropertyNamesForPartner($partnerId);

            return [
                'success' => true,
                'data'    => $properties,
                'message' => __('property.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('Partner get property names failed', [
                'partner_id' => Auth::id(),
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property.messages.retrieved_failed'),
            ];
        }
    }
}

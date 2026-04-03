<?php

namespace App\Services;

use App\Repositories\PropertyTypeRepository\PropertyTypeRepositoryInterface;
use Illuminate\Support\Facades\Log;

class PropertyTypeService
{
    public function __construct(
        private PropertyTypeRepositoryInterface $propertyTypeRepository
    ) {
    }

    /**
     * Create new property type.
     *
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function store(array $data): array
    {
        try {
            $propertyType = $this->propertyTypeRepository->create($data);

            return [
                'success' => true,
                'data' => $propertyType->toArray(),
                'message' => __('property_type.messages.create_success'),
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to create property type', [
                'payload' => $data,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('property_type.messages.create_error'),
            ];
        }
    }

    /**
     * Retrieve property types list.
     *
     * @param array{pagination?: int|null} $filters
     * @return array{success: bool, data: mixed, message: string}
     */
    public function list(array $filters = []): array
    {
        try {
            $data = $this->propertyTypeRepository->getList($filters);

            return [
                'success' => true,
                'data' => $data,
                'message' => __('property_type.messages.fetch_success'),
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to list property types', [
                'filters' => $filters,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('property_type.messages.fetch_error'),
            ];
        }
    }

    /**
     * Retrieve property type detail.
     *
     * @param int $id
     * @return array{success: bool, data: mixed, message: string}
     */
    public function detail(int $id): array
    {
        try {
            $propertyType = $this->propertyTypeRepository->find($id);

            if (! $propertyType) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('property_type.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $propertyType->toArray(),
                'message' => __('property_type.messages.fetch_success'),
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to fetch property type detail', [
                'property_type_id' => $id,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('property_type.messages.fetch_error'),
            ];
        }
    }

    /**
     * Update property type detail.
     *
     * @param int $id
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function update(int $id, array $data): array
    {
        try {
            $propertyType = $this->propertyTypeRepository->find($id);

            if (! $propertyType) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('property_type.messages.not_found'),
                ];
            }

            $propertyType->update($data);

            return [
                'success' => true,
                'data' => $propertyType->refresh()->toArray(),
                'message' => __('property_type.messages.update_success'),
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to update property type', [
                'property_type_id' => $id,
                'payload' => $data,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('property_type.messages.update_error'),
            ];
        }
    }

    /**
     * Update property type status.
     *
     * @param int $id
     * @param array{is_active: bool} $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function updateStatus(int $id, array $data): array
    {
        try {
            $propertyType = $this->propertyTypeRepository->find($id);

            if (! $propertyType) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('property_type.messages.not_found'),
                ];
            }

            $propertyType->update([
                'is_active' => $data['is_active'],
            ]);

            return [
                'success' => true,
                'data' => $propertyType->refresh()->toArray(),
                'message' => $data['is_active']
                    ? __('property_type.messages.activate_success')
                    : __('property_type.messages.deactivate_success'),
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to update property type status', [
                'property_type_id' => $id,
                'payload' => $data,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('property_type.messages.update_status_error'),
            ];
        }
    }
}

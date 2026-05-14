<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PropertyImageRepository\PropertyImageRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PropertyImageService
{
    public function __construct(
        protected PropertyImageRepositoryInterface $propertyImageRepository,
        protected CloudinaryService $cloudinaryService
    ) {
    }

    public function getByPropertyId(int $propertyId): array
    {
        try {
            $images = $this->propertyImageRepository->getByPropertyId($propertyId);

            return [
                'success' => true,
                'data'    => $images,
                'message' => __('property_image.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('PropertyImage getByPropertyId error: ' . $e->getMessage(), [
                'property_id' => $propertyId,
                'error'       => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property_image.messages.retrieved_failed'),
            ];
        }
    }

    public function show(int $id): object|null
    {
        try {
            return $this->propertyImageRepository->find($id);
        } catch (Exception $e) {
            Log::error('PropertyImage show error: ' . $e->getMessage(), [
                'id'    => $id,
                'error' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    public function store(array $data): array
    {
        try {
            $pid = (int) ($data['property_id'] ?? 0);
            $image = $this->propertyImageRepository->create([
                'property_id'         => $pid,
                'image_url'           => $data['image_url'],
                'id_image_cloudinary' => $data['id_image_cloudinary'],
                'image_type'          => $data['image_type'],
                'sort'                => $this->propertyImageRepository->getMaxSortByPropertyId($pid) + 1,
                'created_by'          => Auth::id(),
                'updated_by'          => Auth::id(),
            ]);

            if (! $image) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property_image.messages.create_failed'),
                ];
            }

            return [
                'success' => true,
                'data'    => $image,
                'message' => __('property_image.messages.created_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('PropertyImage store error: ' . $e->getMessage(), [
                'data'  => $data,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property_image.messages.create_failed'),
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $updated = $this->propertyImageRepository->update($id, [
                'image_url'           => $data['image_url'],
                'id_image_cloudinary' => $data['id_image_cloudinary'],
                'image_type'          => $data['image_type'],
                'updated_by'          => Auth::id(),
            ]);

            if (! $updated) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property_image.messages.update_failed'),
                ];
            }

            return [
                'success' => true,
                'data'    => $updated,
                'message' => __('property_image.messages.updated_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('PropertyImage update error: ' . $e->getMessage(), [
                'id'    => $id,
                'data'  => $data,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property_image.messages.update_failed'),
            ];
        }
    }

    public function destroy(int $id): array
    {
        DB::beginTransaction();
        try {
            $image = $this->propertyImageRepository->find($id);
            if (! $image) {
                return [
                    'success' => false,
                    'data'    => null,
                    'message' => __('property_image.messages.not_found'),
                ];
            }

            $this->cloudinaryService->deleteImage($image->id_image_cloudinary);

            $deleted = $this->propertyImageRepository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'data'    => $deleted,
                'message' => __('property_image.messages.deleted_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('PropertyImage destroy error: ' . $e->getMessage(), [
                'id'    => $id,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('property_image.messages.delete_failed'),
            ];
        }
    }

    public function sort(array $data, int $propertyId): bool|null
    {
        try {
            if (empty($data['ids']) || ! is_array($data['ids'])) {
                return false;
            }

            foreach ($data['ids'] as $index => $id) {
                $this->propertyImageRepository->updateWhere(
                    ['id' => $id, 'property_id' => $propertyId],
                    ['sort' => $index + 1]
                );
            }

            return true;
        } catch (Exception $e) {
            Log::error('PropertyImage sort error: ' . $e->getMessage(), [
                'data'  => $data,
                'error' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}

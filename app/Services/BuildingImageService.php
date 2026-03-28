<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BuildingImageRepository\BuildingImageRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Support\Facades\DB;

final class BuildingImageService
{
    /**
     * BuildingImage repository instance
     */
    protected BuildingImageRepositoryInterface $buildingImageRepository;
    /**
     * Cloudinary service instance
     */
    protected CloudinaryService $cloudinaryService;

    /**
     * Constructor
     *
     * @param BuildingImageRepositoryInterface $buildingImageRepository
     */
    public function __construct(
        BuildingImageRepositoryInterface $buildingImageRepository,
        CloudinaryService $cloudinaryService
    ) {
        $this->buildingImageRepository = $buildingImageRepository;
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Get images by building ID
     *
     * @param int $buildingId
     * @return array{success: bool, data: array|null, message: string}
     */
    public function getByBuildingId(int $buildingId): array
    {
        try {
            $images = $this->buildingImageRepository->getByBuildingId($buildingId);

            if (!$images) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('building_image.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $images,
                'message' => __('building_image.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('BuildingImage getByBuildingId error: ' . $e->getMessage(), [
                'building_id' => $buildingId,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('building_image.messages.retrieved_failed'),
            ];
        }
    }

    /**
     * Show building image by ID
     *
     * @param int $id
     * @return object|null
     */
    public function show(int $id): object|null
    {
        try {
            return $this->buildingImageRepository->find($id);
        } catch (Exception $e) {
            Log::error('BuildingImage show error: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Store new building image
     *
     * @param array $data
     * @return array{success: bool, data: array|null, message: string}
     */
    public function store(array $data): array
    {
        try {
            $image = $this->buildingImageRepository->create(
                [
                    'building_id' => $data['building_id'],
                    'image_url' => $data['image_url'],
                    'id_image_cloudinary' => $data['id_image_cloudinary'],
                    'image_type' => $data['image_type'],
                    'sort' => $this->buildingImageRepository->getMaxSortByBuildingId($data['building_id']) + 1,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            if (!$image) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('building_image.messages.create_failed'),
                ];
            }
            return [
                'success' => true,
                'data' => $image,
                'message' => __('building_image.messages.created_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('BuildingImage store error: ' . $e->getMessage(), [
                'data' => $data,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('building_image.messages.create_failed'),
            ];
        }
    }

    /**
     * Update building image
     *
     * @param int $id
     * @param array $data
     * @return array{success: bool, data: bool|null, message: string}
     */
    public function update(int $id, array $data): array
    {
        try {
            $updated = $this->buildingImageRepository->update(
                $id,
                [
                    'image_url' => $data['image_url'],
                    'id_image_cloudinary' => $data['id_image_cloudinary'],
                    'image_type' => $data['image_type'],
                    'updated_by' => Auth::id(),
                ]
            );

            if (!$updated) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('building_image.messages.update_failed'),
                ];
            }

            return [
                'success' => true,
                'data' => $updated,
                'message' => __('building_image.messages.updated_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('BuildingImage update error: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('building_image.messages.update_failed'),
            ];
        }
    }

    /**
     * Destroy building image
     *
     * @param int $id
     * @return array{success: bool, data: bool|null, message: string}
     */
    public function destroy(int $id): array
    {
        DB::beginTransaction();
        try {
            $image = $this->buildingImageRepository->find($id);
            if (!$image) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('building_image.messages.not_found'),
                ];
            }

            $this->cloudinaryService->deleteImage($image->id_image_cloudinary);

            $deleted = $this->buildingImageRepository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'data' => $deleted,
                'message' => __('building_image.messages.deleted_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('BuildingImage destroy error: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('building_image.messages.delete_failed'),
            ];
        }
    }

    /**
     * Sort building images
     *
     * @param array $data
     * @param int $buildingId
     * @return bool | null
     */
    public function sort(array $data, int $buildingId): bool | null
    {
        try {
            if (empty($data['ids']) || !is_array($data['ids'])) {
                return false;
            }

            foreach ($data['ids'] as $index => $id) {
                $this->buildingImageRepository->updateWhere(
                    ['id' => $id, 'building_id' => $buildingId],
                    ['sort' => $index]
                );
            }

            return true;
        } catch (Exception $e) {
            Log::error('BuildingImage sort error: ' . $e->getMessage(), [
                'data' => $data,
                'error' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}

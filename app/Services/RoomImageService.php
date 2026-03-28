<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\RoomImageRepository\RoomImageRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Exception;
use Illuminate\Support\Facades\DB;

final class RoomImageService
{
    /**
     * RoomImage repository instance
     */
    protected RoomImageRepositoryInterface $roomImageRepository;
    protected CloudinaryService $cloudinaryService;

    /**
     * Constructor
     *
     * @param RoomImageRepositoryInterface $roomImageRepository
     * @param CloudinaryService $cloudinaryService
     */
    public function __construct(
        RoomImageRepositoryInterface $roomImageRepository,
        CloudinaryService $cloudinaryService
    ) {
        $this->roomImageRepository = $roomImageRepository;
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Save room images
     * @param int $roomId
     * @param array $images
     * @return void
     */
    public function saveRoomImages($roomId, $images)
    {
        try {
            if (!empty($images) && is_array($images) && $roomId) {
                $imageData = array_map(fn($image) => [
                    'room_id' => $roomId,
                    'image_url' => $image['image_url'],
                    'image_type' => $image['image_type'],
                    'sort' => $image['sort'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $images);

                $this->roomImageRepository->insert($imageData);
            }
        } catch (\Throwable $e) {
            Log::error(__('room.messages.save_images_failed'), [
                'error' => $e->getMessage(),
                'room_id' => $roomId,
            ]);
            throw $e;
        }
    }

    /**
     * Get images by room ID
     *
     * @param int $roomId
     * @return array{success: bool, data: array|null, message: string}
     */
    public function getByRoomId(int $roomId): array
    {
        try {
            $images = $this->roomImageRepository->getByRoomId($roomId);

            if (!$images) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('room_image.messages.not_found'),
                ];
            }

            // Add full_url to each image
            foreach ($images as $image) {
                $image->full_url = config('const.CLOUDINARY_HEADER_IMAGE_URL') . $image->image_url;
            }

            return [
                'success' => true,
                'data' => $images,
                'message' => __('room_image.messages.retrieved_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('RoomImage getByRoomId error: ' . $e->getMessage(), [
                'room_id' => $roomId,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('room_image.messages.retrieved_failed'),
            ];
        }
    }

    /**
     * Show room image by ID
     *
     * @param int $id
     * @return object|null
     */
    public function show(int $id): object|null
    {
        try {
            $result = $this->roomImageRepository->find($id);

            if ($result) {
                $result->full_url = config('const.CLOUDINARY_HEADER_IMAGE_URL') . $result->image_url;
            }

            return $result;
        } catch (Exception $e) {
            Log::error('RoomImage show error: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Store new room image
     *
     * @param array $data
     * @return array{success: bool, data: array|null, message: string}
     */
    public function store(array $data): array
    {
        try {
            $roomId = (int) $data['room_id'];
            $image = $this->roomImageRepository->create(
                [
                    'room_id' => $roomId,
                    'image_url' => $data['image_url'],
                    'id_image_cloudinary' => $data['id_image_cloudinary'],
                    'image_type' => $data['image_type'],
                    'sort' => $this->roomImageRepository->getMaxSortByRoomId($roomId) + 1,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            if (!$image) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('room_image.messages.create_failed'),
                ];
            }
            return [
                'success' => true,
                'data' => $image,
                'message' => __('room_image.messages.created_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('RoomImage store error: ' . $e->getMessage(), [
                'data' => $data,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('room_image.messages.create_failed'),
            ];
        }
    }

    /**
     * Update room image type
     *
     * @param array $updates
     * @return array{success: bool, data: array|null, message: string}
     */
    public function updateType(array $updates): array
    {
        DB::beginTransaction();
        try {
            foreach ($updates as $update) {
                $id = $update['id'];
                $imageType = $update['image_type'];

                $this->roomImageRepository->update(
                    $id,
                    [
                        'image_type' => $imageType,
                        'updated_by' => Auth::id(),
                    ]
                );
            }
            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('room_image.messages.update_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RoomImage updateType error: ' . $e->getMessage(), [
                'updates' => $updates,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('room_image.messages.update_failed'),
            ];
        }
    }

    /**
     * Update room image sort order
     *
     * @param int $imageIdA
     * @param int $imageIdB
     * @return array{success: bool, data: bool|null, message: string}
     */
    public function updateSort(int $roomId, int $imageIdA, int $imageIdB): array
    {
        DB::beginTransaction();
        try {
            $imageA = $this->roomImageRepository->find($imageIdA);
            $imageB = $this->roomImageRepository->find($imageIdB);

            if (!$imageA || !$imageB) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('room_image.messages.not_found'),
                ];
            }

            if ($imageA->room_id !== $roomId || $imageB->room_id !== $roomId) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('room_image.messages.room_mismatch'),
                ];
            }

            $sortA = $imageA->sort;
            $sortB = $imageB->sort;

            if ($sortA > $sortB) {
                // Move A to B (up): decrease the sort of images from sortA+1 to sortB by 1, set A.sort = sortB
                $this->roomImageRepository->updateSortRange(
                    $roomId,
                    $sortB,
                    $sortA - 1,
                    1
                );

                $this->roomImageRepository->update($imageIdA, [
                    'sort' => $sortB,
                    'updated_by' => Auth::id(),
                ]);

                $this->roomImageRepository->updateSortRangeWithUpdatedBy(
                    $roomId,
                    $sortB,
                    $sortA - 1,
                    Auth::id()
                );
            } elseif ($sortA < $sortB) {
                // Move A to B (down): decrease the sort of images from sortA+1 to sortB by 1, set A.sort = sortB
                $this->roomImageRepository->updateSortRange(
                    $roomId,
                    $sortA + 1,
                    $sortB,
                    -1
                );

                $this->roomImageRepository->update($imageIdA, [
                    'sort' => $sortB,
                    'updated_by' => Auth::id(),
                ]);

                $this->roomImageRepository->updateSortRangeWithUpdatedBy(
                    $roomId,
                    $sortA + 1,
                    $sortB,
                    Auth::id()
                );
            }

            DB::commit();

            return [
                'success' => true,
                'data' => true,
                'message' => __('room_image.messages.sort_updated_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RoomImage updateSort error: ' . $e->getMessage(), [
                'roomId' => $roomId,
                'imageIdA' => $imageIdA,
                'imageIdB' => $imageIdB,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('room_image.messages.sort_update_failed'),
            ];
        }
    }

    /**
     * Destroy room image
     *
     * @param int|array $ids
     * @return array{success: bool, data: bool|null, message: string}
     */
    public function destroy(int|array $ids): array
    {
        $idsArray = is_array($ids) ? $ids : [$ids];
        DB::beginTransaction();
        try {
            $deletedImages = [];
            $errors = [];
            foreach ($idsArray as $id) {
                $roomImage = $this->roomImageRepository->find($id);
                Cloudinary::destroy($roomImage->id_image_cloudinary);
                $this->roomImageRepository->delete($id);

                $deletedImages[] = $roomImage;

                $deletedSort = $roomImage->sort;
                $this->roomImageRepository->updateSortRange(
                    $roomImage->room_id,
                    $deletedSort + 1,
                    PHP_INT_MAX,
                    -1
                );
            }
            DB::commit();
            return [
                'success' => true,
                'data' => $deletedImages,
                'message' => __('room_image.messages.deleted_successfully'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('RoomImage destroy error: ' . $e->getMessage(), [
                'ids' => $idsArray,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('room_image.messages.delete_failed'),
            ];
        }
    }
}

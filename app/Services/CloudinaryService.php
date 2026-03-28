<?php

declare(strict_types=1);

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic as Image;
use Exception;

final class CloudinaryService
{
    /**
     * Upload image to Cloudinary
     *
     * @param UploadedFile $file
     * @param string|null $folder
     * @param array $options
     * @return array{success: bool, url: string|null, public_id: string|null, message: string}
     */
    public function uploadImage(UploadedFile $file, string $folder, array $options = []): array
    {
        try {
            // Resize and convert to WebP
            $image = Image::make($file);
            $image->resize(config('const.IMAGE_RESIZE_WIDTH'), null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $tempPath = tempnam(sys_get_temp_dir(), 'webp') . '.webp';
            $image->encode('webp', config('const.WEBP_QUALITY'))->save($tempPath);

            $result = Cloudinary::upload(
                $tempPath,
                array_merge([
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'overwrite' => true,
                    'invalidate' => true,
                ], $options)
            );

            // Clean up temp file
            unlink($tempPath);

            $url = str_replace(
                config('const.CLOUDINARY_HEADER_IMAGE_URL'),
                '',
                $result->getSecurePath()
            );

            return [
                'success' => true,
                'url' => $url,
                'public_id' => $result->getPublicId(),
                'message' => __('cloudinary.messages.upload_success'),
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary upload error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'url' => null,
                'public_id' => null,
                'message' => __('cloudinary.messages.upload_error'),
            ];
        }
    }

    /**
     * Upload multiple images to Cloudinary
     *
     * @param array $files
     * @param string|null $folder
     * @param array $options
     * @return array{success: bool, images: array, message: string}
     */
    public function uploadMultipleImages(array $files, string $folder, array $options = []): array
    {
        try {
            $uploadedImages = [];
            $errors = [];

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $result = $this->uploadImage($file, $folder, $options);
                    if ($result['success']) {
                        $uploadedImages[] = [
                            'url' => $result['url'],
                            'public_id' => $result['public_id'],
                        ];
                    } else {
                        $errors[] = $result['message'];
                    }
                }
            }

            return [
                'success' => count($uploadedImages) > 0,
                'images' => $uploadedImages,
                'message' => count($uploadedImages) > 0
                    ? __('cloudinary.messages.upload_multiple_success', [
                        'count' => count($uploadedImages)
                    ])
                    : __('cloudinary.messages.upload_multiple_failed'),
                'errors' => $errors,
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary upload multiple images error: ' . $e->getMessage(), [
                'files' => $files,
                'folder' => $folder,
                'options' => $options,
                'error' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'images' => [],
                'message' => __('cloudinary.messages.upload_multiple_failed'),
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Delete image from Cloudinary
     *
     * @param string $publicId
     * @return array{success: bool, message: string}
     */
    public function deleteImage(string $publicId): array
    {
        try {
            Cloudinary::destroy($publicId);

            return [
                'success' => true,
                'message' => __('cloudinary.messages.delete_success'),
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage(), [
                'public_id' => $publicId,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => __('cloudinary.messages.delete_error'),
            ];
        }
    }

    public function deleteMultipleImages(array $publicIds): array
    {
        try {
            $deletedCount = 0;
            $errors = [];

            foreach ($publicIds as $publicId) {
                $result = $this->deleteImage($publicId);
                if ($result['success']) {
                    $deletedCount++;
                } else {
                    $errors[] = __(
                        'cloudinary.messages.delete_failed_with_id',
                        ['id' => $publicId]
                    ) . ': ' . $result['message'];
                }
            }

            return [
                'success' => $deletedCount > 0,
                'message' => $deletedCount > 0
                    ? __('cloudinary.messages.delete_multiple_success', [
                        'count' => $deletedCount
                    ])
                    : __('cloudinary.messages.delete_multiple_failed'),
                'deleted_count' => $deletedCount,
                'errors' => $errors,
            ];
        } catch (Exception $e) {
            Log::error('Cloudinary delete multiple images error: ' . $e->getMessage(), [
                'public_ids' => $publicIds,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => __('cloudinary.messages.delete_multiple_failed'),
                'deleted_count' => 0,
                'errors' => [$e->getMessage()],
            ];
        }
    }
}

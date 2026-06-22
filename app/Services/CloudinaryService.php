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
            $tempPath = $this->createWebpTempPath($file);

            $result = Cloudinary::upload(
                $tempPath,
                array_merge([
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'overwrite' => true,
                    'invalidate' => true,
                ], $options)
            );

            unlink($tempPath);

            return $this->formatUploadResult($result);
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
     * Upload partner legal documents (images converted to WebP, PDF kept as-is).
     *
     * @return array{success: bool, url: string|null, public_id: string|null, message: string}
     */
    public function uploadPartnerDocument(UploadedFile $file, string $folder, array $options = []): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $isPdf = $extension === 'pdf' || $file->getMimeType() === 'application/pdf';

        if ($isPdf) {
            return $this->uploadRawUploadedFile($file, $folder, $options);
        }

        return $this->uploadImage($file, $folder, $options);
    }

    /**
     * Upload binary content (signature image, generated PDF, etc.).
     *
     * @return array{success: bool, url: string|null, public_id: string|null, message: string}
     */
    public function uploadBinaryFile(
        string $binaryContent,
        string $folder,
        string $filename,
        bool $convertImageToWebp = false
    ): array {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $tempPath = tempnam(sys_get_temp_dir(), 'cld_bin_') . '.' . $extension;

        try {
            if ($convertImageToWebp) {
                $image = Image::make($binaryContent);
                $image->resize(config('const.IMAGE_RESIZE_WIDTH'), null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $webpPath = tempnam(sys_get_temp_dir(), 'cld_webp_') . '.webp';
                $image->encode('webp', config('const.WEBP_QUALITY'))->save($webpPath);
                unlink($tempPath);
                $tempPath = $webpPath;
            } else {
                file_put_contents($tempPath, $binaryContent);
            }

            $result = Cloudinary::upload($tempPath, [
                'folder' => $folder,
                'resource_type' => 'image',
                'overwrite' => true,
                'invalidate' => true,
            ]);

            return $this->formatUploadResult($result);
        } catch (Exception $e) {
            Log::error('Cloudinary binary upload error: ' . $e->getMessage(), [
                'filename' => $filename,
                'error' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'url' => null,
                'public_id' => null,
                'message' => __('cloudinary.messages.upload_error'),
            ];
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Delete a file previously stored on Cloudinary using the relative path saved in DB.
     */
    public function deleteStoredFile(?string $storedPath): void
    {
        $publicId = $this->extractPublicIdFromStoredPath($storedPath);
        if ($publicId) {
            $this->deleteImage($publicId);
        }
    }

    public function extractPublicIdFromStoredPath(?string $storedPath): ?string
    {
        if (empty($storedPath)) {
            return null;
        }

        $normalizedPath = ltrim($storedPath, '/');
        $withoutVersion = preg_replace('/^v\d+\//', '', $normalizedPath);

        if (empty($withoutVersion)) {
            return null;
        }

        return preg_replace('/\.[^.]+$/', '', $withoutVersion) ?: null;
    }

    /**
     * @return array{success: bool, url: string|null, public_id: string|null, message: string}
     */
    private function uploadRawUploadedFile(UploadedFile $file, string $folder, array $options = []): array
    {
        try {
            $result = Cloudinary::upload(
                $file->getRealPath(),
                array_merge([
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'overwrite' => true,
                    'invalidate' => true,
                ], $options)
            );

            return $this->formatUploadResult($result);
        } catch (Exception $e) {
            Log::error('Cloudinary raw upload error: ' . $e->getMessage(), [
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

    private function createWebpTempPath(UploadedFile $file): string
    {
        $image = Image::make($file);
        $image->resize(config('const.IMAGE_RESIZE_WIDTH'), null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $tempPath = tempnam(sys_get_temp_dir(), 'webp') . '.webp';
        $image->encode('webp', config('const.WEBP_QUALITY'))->save($tempPath);

        return $tempPath;
    }

    /**
     * @param mixed $result
     * @return array{success: bool, url: string|null, public_id: string|null, message: string}
     */
    private function formatUploadResult($result): array
    {
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
